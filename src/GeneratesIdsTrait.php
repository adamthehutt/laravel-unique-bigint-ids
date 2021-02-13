<?php
declare(strict_types=1);

namespace AdamTheHutt\LaravelUniqueBigintIds;

use AdamTheHutt\EloquentConstructedEvent\HasConstructedEvent;
use AdamTheHutt\LaravelUniqueBigintIds\Contracts\IdGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

/**
 * @mixin Model
 */
trait GeneratesIdsTrait
{
    use HasConstructedEvent;

    public function initializeGeneratesIdsTrait()
    {
        $this->incrementing = false;
        $this->casts['id'] = 'int';
    }

    public static function bootGeneratesIdsTrait(): void
    {
        $event = config("unique-bigint-ids.event", "constructed");

        static::registerModelEvent($event, function (IdGenerator $model) {
            $model->generateIdIfMissing();
        });
    }

    public function getIdAttribute()
    {
        return $this->generateIdIfMissing();
    }

    public function generateIdIfMissing()
    {
        return $this->attributes['id']
            ?? $this->generateId();
    }

    /**
     * @return int
     */
    public function generateId()
    {
        switch (Config::get("unique-bigint-ids.strategy")) {
            case "redis":
                return $this->attributes["id"] = $this->generateIdUsingRedis();
            case "uuid_short":
                return $this->attributes["id"] = $this->generateIdUsingUuidShort();
            default:
                return $this->attributes["id"] = $this->generateIdUsingTimestamp();
        }
    }

    /**
     * Return the model's ID only if it has been persisted to the DB
     */
    public function persistedId(): ?int
    {
        return $this->exists ? $this->id : null;
    }

    /**
     * Returns the model ID formatted as a 20-character string:
     * {seconds}-{microseconds}-{pid}
     */
    public function humanReadableId(): string
    {
        $str = (string) $this->attributes['id'];

        return substr($str, 0, 10)."-".substr($str, 10, 6)."-".substr($str, 16, 2);
    }

    /**
     * Javascript can't handle numbers greater than 53 bits, which means it will
     * choke on the big bad 64-bit IDs we're generating. Best to convert them to
     * strings when serializing to JSON.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        if (config("unique-bigint-ids.timestamp.javascript_safe", false)) {
            return parent::jsonSerialize();
        }

        $array = parent::jsonSerialize();

        $maxJavascriptInt = 2**53;
        array_walk_recursive($array, function (&$value, $key) use($maxJavascriptInt) {
            if (is_int($value) && $value > $maxJavascriptInt ) {
                settype($value, 'string');
            }
        });

        return $array;
    }

    private function generateIdUsingTimestamp(): int
    {
        static $pid;
        static $taken = [];

        $precision = config("unique-bigint-ids.timestamp.javascript_safe", false)
            ? 14
            : 16;

        ini_set("precision", (string) $precision);

        $pid = $pid ?? substr((string) getmypid(), -2);
        do {
            $timestamp = microtime(true);
            $timestampSegment = substr(str_pad(str_replace(".", "", (string) $timestamp), $precision, "0"), 0, $precision);
            $stringId = $timestampSegment . $pid;
            $id = (int) $stringId;
        } while (isset($taken[$id]));

        // Put things back where they belong when you're finished with them
        ini_restore("precision");

        // For long running processes, limit maximum array size
        if (count($taken) >= 1000) {
            array_shift($taken);
        }

        return $taken[$id] = $id;
    }

    private function generateIdUsingUuidShort(): int
    {
        return DB::selectOne("SELECT UUID_SHORT() as `id`")->id;
    }

    private function generateIdUsingRedis(): int
    {
        $redisKey = "laravel-unique-bigint-ids-counter";
        $id = Redis::incr($redisKey);
        if ($id < 1000) {
            $id = $this->generateIdUsingTimestamp();
            Redis::getset($redisKey, $id);
        }

        return $id;
    }
}
