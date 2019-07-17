<?php

include "../vendor/autoload.php";

use aliyunsdk\acm\AuthCreds;
use aliyunsdk\acm\Client;
use aliyunsdk\acm\Element;
use aliyunsdk\acm\Endpoint;

$endpoint = new Endpoint("acm.aliyun.com", 8080);
$element = new Element("您的命名空间");
$authCreds = new AuthCreds("您的Access Key", "您的Secret Key");

$client = new Client($endpoint, $element, $authCreds);

$dataId = "com.99xs.com.admin";

$content = json_encode(["name" => "test"]);

$client->write($dataId, $content);

sleep(1); // 等待写入完成

$readContent = $client->read($dataId);

if ($readContent != $content)
{
    throw new Exception("unexpected result, expect = {$content}, but = {$readContent}");
}

$client->remove($dataId);

// 删除后立即监听，通常情况能监听到返回true
echo $client->watch($dataId, $content);