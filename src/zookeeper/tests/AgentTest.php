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
use Hyperf\Zookeeper\Agent;
use Hyperf\Zookeeper\AgentInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Container;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Utils\ApplicationContext;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * @internal
 * @covers \Hyperf\Zookeeper\Agent
 */
class AgentTest extends TestCase
{
    /**
     * @var AgentInterface
     */
    private $agent;

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
        $this->agent = new Agent($container);
    }

    public function testServices()
    {
        $response = $this->agent->services();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertIsArray($response->json());
    }
}
