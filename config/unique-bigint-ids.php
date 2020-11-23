<?php
declare(strict_types=1);

use Carbon\Carbon;

return [
    /**
     * Options are "timestamp", "uuid_short", or "redis"
     */
    'strategy' => env("UNIQUE_BIGINT_ID_STRATEGY", "timestamp"),

    /**
     * When should the ID be generated? The default is a "constructed" event,
     * which fires immediately after a model is constructed and (if applicable)
     * hydrated. This can result in a lot of unnecessary calls, however, so an
     * alternative is to use "creating" (actually any observable event can be
     * used, but "creating" makes the most sense). In that case, an ID won't be
     * generated until the model is about to be saved for the first time.
     * HOWEVER, an ID will be still be generated earlier if code attempts to
     * access it by calling $model->id.
     */
    'event' => env("UNIQUE_BIGINT_ID_EVENT", "constructed"),

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
