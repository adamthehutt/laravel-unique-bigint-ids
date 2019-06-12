<?php
declare(strict_types=1);

return [
    /**
     * Options are "timestamp" or "uuid_short"
     */
    'strategy' => env("UNIQUE_BIGINT_ID_STRATEGY", "timestamp")
];
