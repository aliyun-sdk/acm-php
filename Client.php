<?php

namespace aliyunsdk\acm;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Client
 * @package aliyunsdk\acm
 */
class Client
{
    const GET_SERVER = "diamond-server/diamond";
    const GET_CONFIG = "diamond-server/config.co";
    const SET_CONFIG = "diamond-server/basestone.do?method=syncUpdateAll";
    const DEL_CONFIG = "diamond-server/datum.do?method=deleteAllDatums";

    /**
     * 命名空间
     *
     * @var string
     */
    protected $namespace;

    /**
     * 集群名称
     *
     * @var string
     */
    protected $groupName;

    /**
     * 认证凭证
     *
     * @var AuthCreds
     */
    protected $authCreds;

    /**
     * HTTP客户端
     *
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * 可用请求端点
     *
     * @var Endpoint[]
     */
    protected $endpoints;

    /**
     * Client constructor.
     * @param Endpoint $endpoint
     * @param AuthCreds $authCreds
     * @param string $namespace
     * @param string $groupName
     * @throws \Exception
     */
    public function __construct(Endpoint $endpoint, AuthCreds $authCreds, string $namespace, string $groupName = "DEFAULT_GROUP")
    {
        $this->namespace = $namespace;
        $this->groupName = $groupName;
        $this->authCreds = $authCreds;

        $handler = HandlerStack::create();
        $handler->push($this->addHeaders());

        $this->httpClient = new HttpClient(["handler" => $handler]);

        // 通过Endpoint查询服务IP列表, 以便后面能够通过IP发起请求
        $res = $this->httpClient->get(
            $endpoint->makeURL(self::GET_SERVER)
        );

        $content = $this->handleResponse($res);

        foreach (explode("\n", $content) as $server)
        {
            if (!($server = trim($server)))
            {
                continue;
            }

            $parts = explode(":", $server);

            $this->endpoints[] = new Endpoint($parts[0], $parts[1] ?? 8080);
        }
    }

    /**
     * 读取配置集
     *
     * @param string $dataId
     * @return string
     * @throws \Exception
     */
    public function read(string $dataId): string
    {
        $res = $this->httpClient->get(
            $this->makeURL(self::GET_CONFIG)
            . "?tenant={$this->namespace}"
            . "&dataId={$dataId}"
            . "&group={$this->groupName}"
        );

        return $this->handleResponse($res);
    }

    /**
     * 写入配置集
     *
     * @param string $dataId
     * @param string $content
     * @throws \Exception
     */
    public function write(string $dataId, string $content): void
    {
        $res = $this->httpClient->post(
            $this->makeURL(self::SET_CONFIG),
            [
                "form_params" => [
                    "tenant" => $this->namespace,
                    "dataId" => $dataId,
                    "group" => $this->groupName,
                    "content" => $content,
                ],
            ]
        );

        $this->handleResponse($res);
    }

    /**
     * 删除配置集
     *
     * @param string $dataId
     * @throws \Exception
     */
    public function remove(string $dataId): void
    {
        $res = $this->httpClient->post(
            $this->makeURL(self::DEL_CONFIG),
            [
                "form_params" => [
                    "tenant" => $this->namespace,
                    "dataId" => $dataId,
                    "group" => $this->groupName,
                ],
            ]
        );

        $this->handleResponse($res);
    }

    /**
     * 监听配置集
     *
     * @param string $dataId
     * @param string $content
     * @return string
     * @throws \Exception
     */
    public function watch(string $dataId, string $content): string
    {
        $wordDelimiter = chr(37) . chr(48) . chr(50);
        $lineDelimiter = chr(37) . chr(48) . chr(49);

        $res = $this->httpClient->post(
            $this->makeURL(self::GET_CONFIG),
            [
                "headers" => [
                    "longPullingTimeout" => 30 * 1000,
                ],
                "body" => "Probe-Modify-Request=" . implode($wordDelimiter, [$dataId, $this->groupName, md5($content), $this->namespace]) . $lineDelimiter,
            ]
        );

        return $this->handleResponse($res);
    }

    /**
     * 生成完整URL
     *
     * @param string $path
     * @return string
     */
    private function makeURL(string $path): string
    {
        $key = array_rand($this->endpoints, 1);

        return $this->endpoints[$key]->makeURL($path);
    }

    /**
     * 添加请求头信息
     *
     * @return \Closure
     */
    private function addHeaders(): \Closure
    {
        return function (callable $handler)
        {
            return function (RequestInterface $request, array $options) use ($handler)
            {
                $request = $request
                    ->withHeader("timeStamp", time() * 1000)
                    ->withHeader("Spas-AccessKey", $this->authCreds->getAccessKey());

                $request = $request->withHeader("Spas-Signature", $this->generateSignature($request));

                return $handler($request, $options);
            };
        };
    }

    /**
     * 计算安全签名
     *
     * @param RequestInterface $request
     * @return string
     */
    private function generateSignature(RequestInterface $request): string
    {
        $string = sprintf("%s+%s+%s", $this->namespace, $this->groupName, $request->getHeaderLine("timeStamp"));

        return base64_encode(hash_hmac('sha1', $string, $this->authCreds->getSecretKey(), true));
    }

    /**
     * 处理请求响应
     *
     * @param ResponseInterface $response
     * @return string
     * @throws \Exception
     */
    private function handleResponse(ResponseInterface $response): string
    {
        $content = $response->getBody()->getContents();

        if ($response->getStatusCode() != 200)
        {
            throw new \Exception("get server failed, status code = {$response->getStatusCode()}, content = {$content}.");
        }

        return $content;
    }
}