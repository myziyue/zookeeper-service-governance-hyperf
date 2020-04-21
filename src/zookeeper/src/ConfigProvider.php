<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.myziyue.com
 * @document https://doc.myziyue.com
 * @contact  group@myziyue.com
 * @license  https://github.com/myziyue/zookeeper-service-governance-hyperf/blob/master/LICENSE
 */

namespace Hyperf\Zookeeper;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                \Zookeeper::class => Client::class,
            ],
            'commands' => [
            ],
            'scan' => [
                'paths' => [
                    __DIR__,
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config of zookeeper client.',
                    'source' => __DIR__ . '/../publish/zookeeper.php',
                    'destination' => BASE_PATH . '/config/autoload/zookeeper.php',
                ],
            ],
        ];
    }
}
