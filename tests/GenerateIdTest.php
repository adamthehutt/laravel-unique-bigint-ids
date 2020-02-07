<?php
declare(strict_types=1);

namespace AdamTheHutt\LaravelUniqueBigintIds\Tests;

use Orchestra\Testbench\TestCase;

/**
 * @covers \AdamTheHutt\LaravelUniqueBigintIds\GeneratesIdsTrait
 */
class GenerateIdTest extends TestCase
{
    public $ids = [];

    public $sorted = [];

    /** @test */
    public function it_generates_19_digit_ids()
    {
        $model = new Thingy();
        $this->assertEquals(19, strlen((string) $model->id));
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
}
