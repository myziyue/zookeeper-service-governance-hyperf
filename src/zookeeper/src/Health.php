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

use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Utils\ApplicationContext;

class Health extends Client implements HealthInterface
{
    public function service($serviceName, array $options = []): ZookeeperResponse
    {
        $services = [];
        $servicesPath = $this->getPath();
        if ($this->getClientFactory()->exists($servicesPath)) {
            foreach ($this->getChildren($servicesPath) as $childrenPath) {
                $data = $this->getClientFactory()->get($this->getPath() . '/' . $childrenPath);
                if (!$data) {
                    break;
                }
                $service = json_decode($data, true);
                if (isset($service['Name']) && $serviceName == $service['Name']) {
                    $services[$childrenPath] = [
                        'Service' => $service,
                        'Checks' => [
                            ['Status' => 'passing'] //$this->checks($service)]
                        ]
                    ];
                }
            }
            $this->getResponse()->setBody(json_encode($services));
            $this->getResponse()->setStatusCode(200);
        }
        return $this->getResponse();
    }

    public function checks($service, array $options = []): string
    {
        $response = null;
        $checks = '';
        try {
            if (isset($service['Check']['HTTP'])) {
                $response = ApplicationContext::getContainer()
                    ->get(\GuzzleHttp\Client::class)
                    ->get($service['Check']['HTTP']);
                $this->logger->info(sprintf("http connection : %s . ", $response->getStatusCode()), $this->defaultLoggerContext);
                $checks = $response && $response->getStatusCode() == 200 ? 'passing' : '';
            } elseif (isset($service['Check']['TCP'])) {
                $addr = explode(':', $service['Check']['TCP']);
                $checks = $this->checkTcpStatus($addr[0], (int)$addr[1]) == 200 ? 'passing' : '';
            }
        } catch (GuzzleException $exception) {
            $this->logger->error(sprintf("Zookeeper Check Service Error: %s (%s).", $exception->getMessage(), $exception->getCode()), $this->defaultLoggerContext);
            $this->getResponse()->setStatusCode(500);
        }
        return $checks;
    }

    protected function checkTcpStatus(string $ip, int $port): int
    {
        $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_nonblock($sock);
        socket_connect($sock, $ip, $port);
        socket_set_block($sock);
        $statusCode = 0;
        $exception = [];
        $read = [$sock];
        $write = [$sock];
        $status = socket_select($read, $write, $exception, 5);
        $this->logger->info(sprintf("tcp connection : %s . ", $status), $this->defaultLoggerContext);
        switch ($status) {
            case 2:
                $statusCode = 404;
                break;
            case 1:
                $statusCode = 200;
                break;
            case 0:
                $statusCode = 502;
                break;
        }
        return $statusCode;
    }
}
