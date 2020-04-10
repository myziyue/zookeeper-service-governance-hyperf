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

namespace HyperfTest\Zookeeper;

use GuzzleHttp\Client as GzClient;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Container;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Utils\ApplicationContext;
use HyperfTest\Zookeeper\Stub\Client;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use ReflectionClass;

/**
 * @internal
 * @covers \Hyperf\Zookeeper\Client
 */
class ClientTest extends TestCase
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var \ReflectionMethod
     */
    private $method;

    protected function setUp()
    {
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturn(new NullLogger());
        $container->shouldReceive('get')->with(ClientFactory::class)->andReturn(new ClientFactory($container));
//        $container->shouldReceive('make')->andReturnUsing(function ($name, $options) {
//            if ($name === GzClient::class) {
//                return new GzClient($options['config']);
//            }
//        });
        ApplicationContext::setContainer($container);
        return new Client($container);
    }

    public function testCreatePath()
    {
        $path = '/test/create/path';
        $this->assertSame($this->client->createPath($path), $path);
    }
}
