<?php
declare(strict_types=1);

namespace AdamTheHutt\LaravelUniqueBigintIds;

use AdamTheHutt\EloquentConstructedEvent\HasConstructedEvent;
use AdamTheHutt\LaravelUniqueBigintIds\Contracts\IdGenerator;
use Illuminate\Database\Eloquent\Model;

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
        static::registerModelEvent('constructed', function (IdGenerator $model) {
            $model->attributes['id'] ??= $model->generateId();
        });
    }

    public function generateId(): int
    {
        return Engine::generateId();
    }

    /**
     * Return the model's ID only if it has been persisted to the DB
     */
    public function persistedId(): ?int
    {
        return $this->exists ? $this->id : null;
    }

    /**
     * Returns the model ID formatted as a chunked/hyphenated string.
     */
    public function humanReadableId(): string
    {
        $bin = decbin($this->attributes['id']);

        $timeBits = substr($bin, 0, strlen($bin)-22);
        $nodeBits = substr($bin, -22, 10);
        $randomBits = substr($bin, -12);

        return bindec($timeBits) . '-' . bindec($nodeBits) . '-' . bindec($randomBits);
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
}
