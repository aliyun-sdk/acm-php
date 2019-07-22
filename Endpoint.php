<?php

namespace AliyunSDK\Acm;

/**
 * Class Endpoint
 * @package AliyunSDK\Acm
 */
class Endpoint
{
    /**
     * 请求地址
     *
     * @var string
     */
    private $host;

    /**
     * 请求端口
     *
     * @var integer
     */
    private $port;

    /**
     * Endpoint constructor.
     * @param string $host
     * @param int    $port
     */
    public function __construct(string $host, int $port)
    {
        $this->host = $host ?: "";
        $this->port = $port ?: 80;

        if (!$this->host)
        {
            throw new \InvalidArgumentException("the host can't be empty.");
        }
    }

    /**
     * 生成完整URL
     *
     * @param string $path
     * @return string
     */
    public function makeURL(string $path): string
    {
        return sprintf("http://%s:%d/%s", $this->host, $this->port, $path);
    }
}