<?php
declare(strict_types=1);

namespace AdamTheHutt\LaravelUniqueBigintIds\Tests;

use Carbon\Carbon;
use Orchestra\Testbench\TestCase;

class TimestampPrecisionTest extends TestCase
{
    public function tearDown(): void
    {
        config()->set("unique-bigint-ids.timestamp.javascript_safe", false);

        parent::tearDown();
    }

    /** @test */
    public function can_generate_javascript_safe_integers()
    {
        config()->set("unique-bigint-ids.timestamp.javascript_safe", true);

        $model = new Thingy();

        $this->assertLessThan(2**53-1, $model->id);
    }

    /** @test */
    public function is_still_javascript_safe_in_2100()
    {
        config()->set("unique-bigint-ids.timestamp.javascript_safe", true);

        $model = new Thingy();

        $pid = substr((string) getmypid(), -2);
        $timestampPortion = preg_replace("/$pid$/", "", (string) $model->id);
        $secondsOnly = substr($timestampPortion, 0, strlen((string) time()));
        $resetTimestampPortion = bcsub($secondsOnly, (string) time());
        $adjustedTimestampPortion = bcadd($resetTimestampPortion, (string) strtotime("2100-12-31 23:59:59"));
        $idWouldBe = $adjustedTimestampPortion . "0000" . $pid;

        $this->assertLessThan(2**53-1, $idWouldBe);
    }
}
