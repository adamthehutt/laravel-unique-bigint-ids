<?php
declare(strict_types=1);

namespace AdamTheHutt\LaravelUniqueBigintIds\Tests;

use Illuminate\Support\Facades\Redis;
use Orchestra\Testbench\TestCase;

/**
 * @covers \AdamTheHutt\LaravelUniqueBigintIds\GeneratesIdsTrait
 */
class GenerateIdTest extends TestCase
{
    public $ids = [];

    public $sorted = [];

    /** @test */
    public function it_generates_18_plus_digit_ids()
    {
        config()->set("unique-bigint-ids.timestamp.javascript_safe", false);

        $model = new Thingy();
        $this->assertGreaterThanOrEqual(18, strlen((string) $model->id));
    }

    /** @test */
    public function it_generates_sequential_ids()
    {
        $model = new Thingy();
        for ($i = 1; $i <= 100000; $i++) {
            $id             = $model->generateId();
            $this->ids[]    = $id;
            $this->sorted[] = $id;
        }

        sort($this->sorted);

        $this->assertEquals($this->sorted, $this->ids);
    }

    /** @test */
    public function it_generates_unique_ids()
    {
        $model = new Thingy();
        for ($i = 1; $i <= 100000; $i++) {
            $id             = $model->generateId();
            $this->ids[$id] = $id;
        }

        $this->assertCount(100000, $this->ids);
    }

    /** @test */
    public function it_generates_id_on_construction()
    {
        $model = new Thingy();
        $this->assertNotEmpty($model->id);
    }

    /** @test */
    public function it_uses_the_redis_strategy()
    {
        $oldStrategy = config("unique-bigint-ids.strategy");
        config()->set("unique-bigint-ids.strategy", "redis");

        $model = new Thingy();

        $this->assertGreaterThan(10000000, $model->id);
        $this->assertTrue((bool) Redis::exists("laravel-unique-bigint-ids-counter"));

        config()->set("unique-bigint-ids.strategy", $oldStrategy);
    }
}
