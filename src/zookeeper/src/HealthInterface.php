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

interface HealthInterface
{
    public function service($serviceName, array $options = []): ZookeeperResponse;

    public function checks($serviceName, array $options = []): string;
}
