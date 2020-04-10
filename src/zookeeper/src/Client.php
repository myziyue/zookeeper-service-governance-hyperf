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

use Hyperf\Contract\ConfigInterface;
use Hyperf\Zookeeper\Exception\ClientException;
use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class Client
{
    /**
     * @var string
     */
    private $defaultServer = '127.0.0.1:2181';
    /**
     * @var string
     */
    private $defaultPath = '/hyperf-services';

    /**
     * @var \Swoole\Zookeeper
     */
    private $clientFactory;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;
    /**
     * @var ZookeeperResponse
     */
    private $zookeeperResponse;

    /**
     * @var array
     */
    public $defaultLoggerContext = [
        'component' => 'zookeeper',
    ];

    public function __construct(ContainerInterface $container, LoggerInterface $logger = null)
    {
        $this->config = $container->get(ConfigInterface::class);
        $this->logger = $logger ?: new NullLogger();
        $this->zookeeperResponse = $container->get(ZookeeperResponse::class);
        try {
            $this->clientFactory = new \Swoole\Zookeeper($this->config->get('zookeeper.server', $this->defaultServer), 2.5);
            \Swoole\Zookeeper::setDebugLevel(1);
            $this->defaultPath = $this->config->get('zookeeper.path', $this->defaultPath);
        } catch (ClientException $exception) {
            $this->logger->error(sprintf('Zookeeper connect error: %s (%s).', $exception->getMessage(), $exception->getCode()), $this->defaultLoggerContext);
        }
    }

    public function __call($name, $arguments)
    {
        return $this->clientFactory->{$name}(...$arguments);
    }

    public function getClientFactory()
    {
        return $this->clientFactory;
    }

    public function getPath()
    {
        return $this->defaultPath;
    }

    public function getResponse()
    {
        return $this->zookeeperResponse;
    }

    public function createPath(string $path, string $value = '')
    {
        $parts = explode("/", $path);
        $parts = array_filter($parts);
        $subPath = "";
        while (count($parts) > 1) {
            $subPath .= '/' . array_shift($parts);
            if (!$this->getClientFactory()->exists($subPath)) {
                $this->getClientFactory()->create($subPath);
            }
        }

        if (!$this->getClientFactory()->exists($path)) {
            $this->getClientFactory()->create($path, $value);
        }
    }
}
