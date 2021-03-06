<?php

declare(strict_types=1);
/**
 * This file is part of Yunhu.
 *
 * @link     https://www.myziyue.com
 * @contact  zhiming.bi@myziyue.com
 * @license  https://github.com/myziyue/zookeeper-service-governance-hyperf/blob/master/LICENSE
 */

namespace Hyperf\Zookeeper\Pool;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ConnectionInterface;
use Hyperf\Pool\Pool;
use Hyperf\Utils\Arr;
use Hyperf\Zookeeper\Zookeeper;
use Psr\Container\ContainerInterface;

class ZookeeperPool extends Pool
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $config;

    public function __construct(ContainerInterface $container, string $name)
    {
        $this->name = $name;
        $config = $container->get(ConfigInterface::class);
        $key = sprintf('zookeeper.%s', $this->name);
        if (! $config->has($key)) {
            throw new \InvalidArgumentException(sprintf('config[%s] is not exist!', $key));
        }

        $this->config = $config->get($key);
        $options = Arr::get($this->config, 'pool', []);

        parent::__construct($container, $options);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    protected function createConnection(): ConnectionInterface
    {
        return new Zookeeper($this->container, $this, $this->config);
    }
}