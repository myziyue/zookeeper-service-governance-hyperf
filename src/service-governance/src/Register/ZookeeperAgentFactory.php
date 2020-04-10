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

namespace Hyperf\ServiceGovernance\Register;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Zookeeper\Agent;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class ZookeeperAgentFactory
{
    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var ConfigInterface
     */
    protected $config;

    public function __invoke(ContainerInterface $container)
    {
        return new Agent($container);
    }
}
