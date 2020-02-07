<?php
declare(strict_types=1);

namespace AdamTheHutt\LaravelUniqueBigintIds;

use AdamTheHutt\EloquentConstructedEvent\HasConstructedEvent;
use AdamTheHutt\LaravelUniqueBigintIds\Contracts\IdGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * @mixin Model
 */
trait GeneratesIdsTrait
{
    use HasConstructedEvent;

    private static int $lastId;

    public function initializeGeneratesIdsTrait()
    {
        $this->incrementing = false;
        $this->casts['id'] = 'int';
    }

    public static function bootGeneratesIdsTrait(): void
    {
        if (!isset(self::$lastId)) {
            $timestamp = decbin(round(microtime(true) * 1000));                        // 41 bits (eventually 42)
            $node      = decbin(pow(2,9) - 1 + self::nodeId());                     // 10 bits (node between 1 and 512)
            $random    = decbin(pow(2,11)- 1 + mt_rand(1, pow(2,11)-1)); // 12 bits

            self::$lastId = bindec($timestamp . $node . $random);
        }

        static::registerModelEvent('constructed', function (IdGenerator $model) {
            $model->attributes['id'] ??= $model->generateId();
        });
    }

    public function generateId(): int
    {
        return self::$lastId++;
    }

    /**
     * Return the model's ID only if it has been persisted to the DB
     */
    public function persistedId(): ?int
    {
        return $this->exists ? $this->id : null;
    }

    /**
     * Returns the model ID formatted as a 21-character string.
     */
    public function humanReadableId(): string
    {
        $str = (string) $this->attributes['id'];

        return substr($str, 0, 7)."-".substr($str, 7, 7)."-".substr($str, 14, 7);
    }

    /**
     * Javascript can't handle numbers greater than 53 bits, which means it will
     * choke on the big bad 64-bit IDs we're generating. Best to convert them to
     * strings when serializing to JSON.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        $array = parent::jsonSerialize();

        $maxJavascriptInt = 2**53;
        array_walk_recursive($array, function (&$value, $key) use($maxJavascriptInt) {
            if (is_int($value) && $value > $maxJavascriptInt ) {
                settype($value, 'string');
            }
        });

        return $array;
    }

    public static function nodeId(): int
    {
        return Config::get("unique_bigint_ids.node", 1);
    }
}
