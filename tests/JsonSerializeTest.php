<?php
declare(strict_types=1);

namespace AdamTheHutt\LaravelUniqueBigintIds\Tests;

use Orchestra\Testbench\TestCase;

/**
 * @covers \AdamTheHutt\LaravelUniqueBigintIds\GeneratesIdsTrait
 */
class JsonSerializeTest extends TestCase
{
    /** @test */
    public function it_serializes_long_ids_to_strings()
    {
        $model = new Thingy();
        $result = $model->jsonSerialize();

        $this->assertTrue(is_string($result['id']));
    }

    /** @test */
    public function it_descends_into_relations()
    {
        $model = new Thingy();
        $model->buddy()->associate(new Thingy());

        $result = $model->jsonSerialize();

        $this->assertIsString($result['buddy']['id']);
    }

    /** @test */
    public function ignores_if_ids_are_already_safe()
    {
        config()->set("unique-bigint-ids.timestamp.javascript_safe", true);

        $model = new Thingy();
        $result = $model->jsonSerialize();

        $this->assertTrue(is_integer($result['id']));
    }
}
