<?php
declare(strict_types=1);

namespace AdamTheHutt\LaravelUniqueBigintIds;

use Illuminate\Support\Facades\Config;

abstract class Engine
{
    private static int $lastId;
    
    public static function generateId(): int 
    {
        if (!isset(self::$lastId)) {
            $timestamp = decbin(round(microtime(true) * 1000) - self::epoch()); // 41 bits (eventually 42)
            $node      = decbin(pow(2,9) - 1 + self::nodeId());                      // 10 bits (node between 1 and 512)
            $random    = decbin(pow(2,11)- 1 + mt_rand(1, pow(2,11)-1));  // 12 bits

            self::$lastId = bindec($timestamp . $node . $random);
        }

        return self::$lastId++;
    }
    
    public static function nodeId(): int
    {
        return Config::get("unique_bigint_ids.node", 1);
    }

    public static function epoch(): int
    {
        return Config::get("unique_bigint_ids.epoch", 1514764800000);
    }
}