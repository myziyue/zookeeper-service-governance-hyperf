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

namespace Hyperf\ServiceGovernance;

use Hyperf\ServiceGovernance\Listener\RegisterServiceListener;
use Hyperf\ServiceGovernance\Register\ConsulAgent;
use Hyperf\ServiceGovernance\Register\ConsulAgentFactory;
use Hyperf\ServiceGovernance\Register\ZookeeperAgent;
use Hyperf\ServiceGovernance\Register\ZookeeperAgentFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                ConsulAgent::class => ConsulAgentFactory::class,
                ZookeeperAgent::class => ZookeeperAgentFactory::class,
            ],
            'listeners' => [
                RegisterServiceListener::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
        ];
    }
}
