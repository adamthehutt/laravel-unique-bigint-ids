<?php

return [
    'node' => env('UNIQUE_BIGINT_IDS_SERVER_NODE', 1),          // Must be unique per server; integer between 1 and 512
    'epoch' => env('UNIQUE_BIGINT_IDS_EPOCH', 1514764800000)    // Starting timestamp (in milliseconds); default is January 1, 2018 at midnight
];