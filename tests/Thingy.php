<?php
declare(strict_types=1);

namespace AdamTheHutt\LaravelUniqueBigintIds\Tests;

use AdamTheHutt\LaravelUniqueBigintIds\Contracts\IdGenerator;
use AdamTheHutt\LaravelUniqueBigintIds\GeneratesIdsTrait;
use Illuminate\Database\Eloquent\Model;

class Thingy extends Model implements IdGenerator
{
    use GeneratesIdsTrait;

    protected $appends = ['buddy'];

    public function buddy()
    {
        return $this->belongsTo(Thingy::class);
    }

    public function getBuddyAttribute()
    {
        return $this->relations['buddy'] ?? null;
    }
}
