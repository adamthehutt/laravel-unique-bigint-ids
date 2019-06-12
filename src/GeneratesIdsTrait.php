<?php
declare(strict_types=1);

namespace AdamTheHutt\LaravelUniqueBigintIds;

use AdamTheHutt\LaravelUniqueBigintIds\Contracts\IdGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @mixin Model
 */
trait GeneratesIdsTrait
{
    /** @var array */
    protected $observables = ['constructed'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->fireModelEvent('constructed');
    }

    public static function bootGeneratesIdsTrait(): void
    {
        static::registerModelEvent('constructed', function (IdGenerator $model) {
            isset($model->id) || $model->generateId();
        });
    }

    public function generateId(): int
    {
        $strategy = Config::get("unique-bigint-ids.strategy");
        $method   = "generateIdUsing".Str::studly($strategy);

        return $this->attributes["id"] = $this->$method();
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

    private function generateIdUsingTimestamp(): int
    {
        static $pid;
        static $taken = [];

        // Need full microsecond precision
        ini_set("precision", "16");

        $pid = $pid ?? substr((string) getmypid(), -2);
        do {
            $microtime = str_pad(str_replace(".", "", microtime(true)), 16, "0");
            $stringId  = $microtime . $pid;
            $id        = (int) $stringId;
        } while (isset($taken[$id]));

        // Put things back where they belong when you're finished with them
        ini_restore("precision");

        return $taken[$id] = $id;
    }

    private function generateIdUsingUuidShort(): int
    {
        return DB::selectOne("SELECT UUID_SHORT() as `id`")->id;
    }
}
