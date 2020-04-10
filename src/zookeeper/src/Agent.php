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

class Agent extends Client implements AgentInterface
{
    public function services(): ZookeeperResponse
    {
        $services = [];
        $servicesPath = $this->getPath();
        if($this->getClientFactory()->exists($servicesPath)) {
            foreach ($this->getChildren($servicesPath) as $childrenPath) {
                $servicePath = $this->getPath() . '/' . $childrenPath;
                $services[$childrenPath] = json_decode($this->getClientFactory()->get($servicePath), true);
            }
        } else {
            $this->createPath($servicesPath);
        }
        $this->getResponse()->setBody(json_encode($services));
        if($services) {
            $this->getResponse()->setStatusCode(200);
        } else {
            $this->getResponse()->setStatusCode(0);
        }
        return $this->getResponse();
    }

    public function registerService(array $service): ZookeeperResponse
    {
        $servicePath = $this->getPath() . '/' . $service['ID'];
        // 判断path是否存在，不存在则创建并保存服务
        if(!$this->getClientFactory()->exists($servicePath)) {
            $this->createPath($servicePath, json_encode($service));
            $this->getResponse()->setStatusCode(200);
        } else {
            $this->getClientFactory()->set($servicePath, json_encode($service));
            $this->getResponse()->setStatusCode(0);
        }

        return $this->getResponse();
    }

    public function deregisterService($serviceId): ZookeeperResponse
    {
        $servicePath = $this->getPath() . '/' . $serviceId;
        // 判断path是否存在，不存在则创建并保存服务
        if(!$this->getClientFactory()->exists($servicePath)) {
            $this->getClientFactory()->delete($servicePath);
        }
        return $this->getResponse();
    }

}
