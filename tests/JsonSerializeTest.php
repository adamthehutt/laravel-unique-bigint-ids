<?php
declare(strict_types=1);

namespace AdamTheHutt\LaravelUniqueBigintIds\Tests;

use PHPUnit\Framework\TestCase;

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
}
