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

use Hyperf\Utils\Arr;
use Psr\Http\Message\ResponseInterface;

/**
 * @method string getReasonPhrase()
 */
class ZookeeperResponse
{
    /**
     * @var string
     */
    private $body;
    /**
     * @var int
     */
    private $statusCode;

    public function setStatusCode(int $statusCode)
    {
        $this->statusCode = $statusCode;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setBody(string $body)
    {
        $this->body = $body;
    }
    public function getBody()
    {
        return $this->body;
    }

    public function json(string $key = null, $default = null)
    {
        $data = json_decode((string) $this->getBody(), true);
        if (! $key) {
            return $data;
        }
        return Arr::get($data, $key, $default);
    }
}
