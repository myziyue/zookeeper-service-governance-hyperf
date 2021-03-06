<?php

declare(strict_types=1);
/**
 * This file is part of Yunhu.
 *
 * @link     https://www.myziyue.com
 * @contact  zhiming.bi@myziyue.com
 * @license  https://github.com/myziyue/zookeeper-service-governance-hyperf/blob/master/LICENSE
 */

namespace Hyperf\Zookeeper;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Pool\Connection as BaseConnection;
use Hyperf\Pool\Exception\ConnectionException;
use Hyperf\Pool\Pool;
use Hyperf\Zookeeper\Exception\InvalidNoExistsPathException;
use Hyperf\Zookeeper\Exception\InvalidZookeeperArgumentException;
use Psr\Container\ContainerInterface;
use swoole\Zookeeper as SwZookeeper;

class Zookeeper extends BaseConnection implements ConnectionInterface
{
    /**
     * @var \Zookeeper
     */
    protected $connection;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $zkHosts;

    /**
     * @var array
     */
    protected $config = [
        'server' => '127.0.0.1:2181',
        'path' => '/hyperf-services',
        'timeout' => 1000,
    ];

    /**
     * @var ZookeeperResponse
     */
    private $zookeeperResponse;

    /**
     * @var null
     */
    private $connectCallBackFunc = null;
    /**
     * @var array
     */
    private $watcherCallbackFunc = [];

    public function __construct(ContainerInterface $container, Pool $pool, array $config)
    {
        parent::__construct($container, $pool);
        $this->config = array_replace($this->config, $config);
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->zookeeperResponse = $container->get(ZookeeperResponse::class);

        $this->reconnect();
    }

    public function __call($name, $arguments)
    {
        return $this->connection->{$name}(...$arguments);
    }

    public function getActiveConnection()
    {
        if ($this->check()) {
            return $this;
        }
        $this->reconnect();

        return $this;
    }

    public function reconnect(): bool
    {
        $this->zkHosts = $this->config['server'];
        $zkScheme = $this->config['scheme'];
        $zkCert = $this->config['cert'];
        $timeout = $this->config['timeout'];

        try {
            $zookeeper = new SwZookeeper($this->zkHosts, $timeout);
            SwZookeeper::setDebugLevel(1);
        } catch (SwZookeeperException $ex) {
            throw new ConnectionException("Connection reconnect failed : {$ex->getMessage()} | {$this->zkHosts}");
        }

        if ($zkScheme) {
            $zookeeper->addAuth($zkScheme, $zkCert);
        }

        $this->connection = $zookeeper;
        $this->lastUseTime = microtime(true);

        return true;
    }

    public function close(): bool
    {
        $this->connection->close();
        return true;
    }

    public function release(): void
    {
        parent::release();
    }

    /**
     * Check if the connection has been established.
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        $ret = $this->connection->getState();
        $this->logger->debug("Zookeeper state : {$ret} | {$this->zkHosts}");
        return SwZookeeper::CONNECTED_STATE == $ret ? TRUE : FALSE;
    }

    /**
     *  Gets the data associated with a node synchronously
     *
     * @param $node
     * @return string
     */
    public function get(string $node): string
    {
        if (!$this->connection->exists($node)) {
            $this->logger->error("Node：{$node} does not exist. | {$this->zkHosts}");
            return "";
        }
        return $this->connection->get($node);
    }

    /**
     * Sets the data associated with a node
     *
     * @param $node
     * @param $value
     */
    public function set(string $node, string $value)
    {
        if (!$this->connection->exists($node)) {
            $this->logger->debug("Node：{$node} does not exist，Start creating nodes. | {$this->zkHosts}");
            $this->makePath($node);
            $this->makeNode($node, $value);
        } else {
            $this->connection->set($node, $value);
        }

    }

    /**
     * 根据路径分隔符，创建节点
     *
     * @param $node
     * @param string $value
     */
    protected function makePath(string $node, string $value = '')
    {
        $parts = explode("/", $node);
        $parts = array_filter($parts);
        $subPath = "";
        while (count($parts) > 1) {
            $subPath .= '/' . array_shift($parts);
            if (!$this->connection->exists($subPath)) {
                $this->makeNode($subPath, $value);
            }
        }
    }

    /**
     * Create a node synchronously
     *
     * @param $node
     * @param $value
     * @param array $options
     * @return mixed
     */
    protected function makeNode(string $node, string $value, array $options = [])
    {
        if (empty($options)) {
            $options = [
                [
                    'perms' => SwZookeeper::PERM_ALL,
                    'scheme' => 'world',
                    'id' => 'anyone'
                ]
            ];
        }
        $this->logger->debug("Create Node : {$node} , value : {$value} | {$this->zkHosts}");
        return $this->connection->create($node, $value, $options);
    }

    /**
     * Lists the children of a node synchronously
     *
     * @param $node
     * @return mixed
     */
    public function getChildren(string $node)
    {
        if (strlen($node) > 1 && preg_match('@/$@', $node)) {
            $node = substr($node, 0, -1);
        }
        return $this->connection->getChildren($node);
    }

    /**
     * @param $node
     * @param callable $callback
     * @return bool
     */
    public function watch(string $node, callable $callback): bool
    {
        if (!is_callable($callback)) {
            throw new InvalidZookeeperArgumentException("Invalid callback function.");
        }

        if ($this->connection->exists($node)) {
            if (!isset($this->watcherCallbackFunc[$node])) {
                $this->watcherCallbackFunc[$node] = [];
            }

            if (!in_array($callback, $this->watcherCallbackFunc[$node])) {
                $this->watcherCallbackFunc[$node][] = $callback;
                $this->connection->get($node, [$this, 'watchCallback']);
                return true;
            }
            return false;
        } else {
            throw new InvalidNoExistsPathException("Node {$node} does not exists.");
        }
    }


    /**
     * Watch Callback
     * @param $type
     * @param $state
     * @param $node
     * @return mixed|null
     */
    public function watchCallback(int $type, string $state, string $node)
    {
        if (!isset($this->watcherCallbackFunc[$node])) {
            throw new InvalidZookeeperArgumentException("Invalid callback function.");
        }

        foreach ($this->watcherCallbackFunc[$node] as $watcherCallback) {
            $this->connection->get($node, [$this, 'watchCallback']);
            return call_user_func($watcherCallback, [$this->connection, $node]);
        }
    }

    /**
     * Cacel Watch Callback
     * @param string $node
     * @param callable $callback
     * @return bool
     */
    public function cacelWatch(string $node, callable $callback = null)
    {
        if (isset($this->watcherCallbackFunc[$node])) {
            if (empty($callback)) {
                $this->watcherCallbackFunc[$node] = [];
                $this->connection->get($node, [$this, 'watchCallback']);
                return true;
            } else {
                $key = array_search($callback, $this->watcherCallbackFunc[$node]);
                if ($key !== false) {
                    unset($this->watcherCallbackFunc[$node][$key]);
                    $this->connection->get($node, [$this, 'watchCallback']);
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    }

    public function createPath(string $path, string $value = '')
    {
        $parts = explode("/", $path);
        $parts = array_filter($parts);
        $subPath = "";
        while (count($parts) > 1) {
            $subPath .= '/' . array_shift($parts);
            if (!$this->connection->exists($subPath)) {
                $this->connection->create($subPath);
            }
        }

        if (!$this->connection->exists($path)) {
            $this->connection->create($path, $value);
        }
    }

    public function getClientFactory()
    {
        return $this->connection;
    }

    public function getPath()
    {
        return $this->config['path'];
    }

    public function getResponse()
    {
        return $this->zookeeperResponse;
    }
}