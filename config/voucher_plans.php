<?php

return [
    '1hour' => [
        'name' => '1 Hour',
        'session_timeout' => 3600,
        'rate_limit' => '2M/5M',
    ],
    '24hour' => [
        'name' => '24 Hours',
        'session_timeout' => 86400,
        'rate_limit' => '3M/8M',
    ],
];
