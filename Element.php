<?php

namespace aliyunsdk\acm;

use function GuzzleHttp\Psr7\build_query;

/**
 * Class Element
 * @package aliyunsdk\acm
 */
class Element
{
    /**
     * 命名空间
     *
     * @var string
     */
    private $namespace;

    /**
     * 集群名称
     *
     * @var string
     */
    private $groupName;

    /**
     * 配置集ID
     *
     * @var string
     */
    private $dataId;

    /**
     * Element constructor.
     * @param string $namespace
     * @param string $groupName
     * @param string $dataId
     */
    public function __construct(string $namespace, string $groupName = "DEFAULT_GROUP", string $dataId = "")
    {
        if (!$namespace)
        {
            throw new \InvalidArgumentException("the namespace can't be empty.");
        }

        $this->namespace = $namespace;
        $this->groupName = $groupName;
        $this->dataId = $dataId;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getGroupName(): string
    {
        return $this->groupName;
    }

    /**
     * @return string
     */
    public function getDataId(): string
    {
        return $this->dataId;
    }

    /**
     * @param string $dataId
     * @return Element
     */
    public function withDataId(string $dataId): Element
    {
        $clone = clone $this;
        $clone->dataId = $dataId;

        return $clone;
    }

    /**
     * 转换成数组
     *
     * @param array $prepend
     * @return array
     */
    public function toArray(array $prepend = []): array
    {
        return array_merge($prepend, [
            "tenant" => $this->namespace,
            "dataId" => $this->dataId,
            "group" => $this->groupName,
        ]);
    }

    /**
     * 转换成Query参数
     *
     * @param array $prepend
     * @return string
     */
    public function toQuery(array $prepend = []): string
    {
        return build_query($this->toArray($prepend));
    }
}