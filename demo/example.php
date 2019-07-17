<?php

include "../vendor/autoload.php";

use aliyunsdk\acm\AuthCreds;
use aliyunsdk\acm\Client;
use aliyunsdk\acm\Endpoint;

$endpoint = new Endpoint("acm.aliyun.com", 8080);
$authCreds = new AuthCreds("您的ACCESS KEY", "您的SECRET KEY");

$client = new Client($endpoint, $authCreds, "您的命名空间");

$dataId = "com.99xs.com.admin";

$content = json_encode(["name" => "test"]);

$client->write($dataId, $content);

sleep(1); // wait for write

$readContent = $client->read($dataId);

if ($readContent != $content)
{
    throw new Exception("unexpected result, expect = {$content}, but = {$readContent}");
}

echo $client->watch($dataId, $content);