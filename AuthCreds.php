<?php

namespace aliyunsdk\acm;

/**
 * Class AuthCreds
 * @package aliyunsdk\acm
 */
class AuthCreds
{
    /**
     * Access Key
     *
     * @var string
     */
    private $accessKey;

    /**
     * Secret Key
     *
     * @var string
     */
    private $secretKey;

    /**
     * AuthCreds constructor.
     * @param string $accessKey
     * @param string $secretKey
     */
    public function __construct(string $accessKey, string $secretKey)
    {
        if (!$accessKey || !$secretKey)
        {
            throw new \InvalidArgumentException("the access key and secret key can't be empty.");
        }

        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
    }

    /**
     * @return string
     */
    public function getAccessKey(): string
    {
        return $this->accessKey;
    }

    /**
     * @return string
     */
    public function getSecretKey(): string
    {
        return $this->secretKey;
    }
}