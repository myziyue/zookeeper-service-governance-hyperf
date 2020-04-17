<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

return [
    'default' => [
        'server' => env('ZK_SERVER', '127.0.0.1:2181'),
        'scheme' => env('ZK_SCHEME', null),
        'cert' => env('ZK_CERT', null),
        'timeout' => 1000,
        'path' => env('ZK_CONFIG_PATH', '/hyperf/services'),
        'pool' => [
            'min_connections' => (int)env('ZK_MIN_CONNECTIONS', 1),
            'max_connections' => (int)env('ZK_MAX_CONNECTIONS', 10),
            'connect_timeout' => (float)env('ZK_CONNECT_TIMEOUT', 10.0),
            'wait_timeout' => (float)env('ZK_WAIT_TIMEOUT', 3.0),
            'heartbeat' => (int)env('ZK_HEARTBEAT', -1),
            'max_idle_time' => (float)env('ZK_MAX_IDLE_TIME', 60),
        ],
    ],
];
