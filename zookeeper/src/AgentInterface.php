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

namespace Hyperf\Zookeeper;

interface AgentInterface
{
    public function services(): ZookeeperResponse;

    public function registerService(array $service): ZookeeperResponse;

    public function deregisterService($serviceId): ZookeeperResponse;
}
