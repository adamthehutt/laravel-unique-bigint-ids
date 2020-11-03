<?php
declare(strict_types=1);

use Carbon\Carbon;

return [
    /**
     * Options are "timestamp" or "uuid_short"
     */
    'strategy' => env("UNIQUE_BIGINT_ID_STRATEGY", "timestamp"),

    'timestamp' => [

        /**
         * Javascript does not support integers greater than 2^53-1. By default this
         * library uses microsecond precision to generate IDs using the timestamp
         * strategy. This results in 18-digit IDs that are larger than Javascript's
         * limit, which can cause issues for JSON encoding. If that limitation is
         * acceptable, you can leave this setting false. Alternatively, set
         * this to true for reduced-precision timestamps that will generate
         * javascript-safe IDs.
         */
        'javascript_safe' => env("UNIQUE_BIGINT_ID_JAVASCRIPT_SAFE", false)
    ]
];
