<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Xin\RedisCollection;

use Redis;

abstract class HashCollection
{
    use CacheKeyTrait;

    public const DEFAULT_KEY = 'swoft:none';

    public const DEFAULT_VALUE = 'none';

    /**
     * redis key.
     * @var string
     */
    protected $prefix;

    /**
     * @var int
     */
    protected $ttl = 0;

    /**
     * 是否认为当前HASH一定存在.
     * @var bool
     */
    protected $exist = false;

    /**
     * 从DB中读取对应的全部列表.
     * @param mixed $parentId
     * @return array
     */
    abstract public function reload($parentId);

    /**
     * 返回Redis实例.
     * @return Redis
     */
    abstract public function redis();

    /**
     * Redis数据初始化.
     * @param mixed $parentId
     */
    public function initialize($parentId)
    {
        $hash = [];
        if ($this->isInitialize()) {
            $hash = $this->reload($parentId);
            $hash[static::DEFAULT_KEY] = static::DEFAULT_VALUE;
            $key = $this->getCacheKey($parentId);

            $this->redis()->hMset($key, $hash);

            // 增加超时时间配置
            if (is_int($this->getTtl()) && $this->getTtl() > 0) {
                $this->redis()->expire($key, $this->getTtl());
            }
        }

        return $hash;
    }

    /**
     * 当前列表是否存在.
     * @param mixed $parentId
     * @return mixed
     */
    public function exist($parentId)
    {
        $key = $this->getCacheKey($parentId);

        return $this->redis()->exists($key);
    }

    /**
     * 查询所有数据.
     * @param mixed $parentId
     * @return array
     */
    public function get($parentId)
    {
        $key = $this->getCacheKey($parentId);
        $res = $this->redis()->hGetAll($key);
        if (empty($res)) {
            $res = $this->initialize($parentId);
        }

        unset($res[static::DEFAULT_KEY]);
        return $res;
    }

    /**
     * 将元素插入到列表.
     * @param mixed $parentId
     * @param string $hkey
     * @param string $hvalue
     * @return false|int
     */
    public function set($parentId, $hkey, $hvalue)
    {
        if (! $this->exist($parentId)) {
            $this->initialize($parentId);
        }

        $key = $this->getCacheKey($parentId);

        return $this->redis()->hSet($key, (string) $hkey, $hvalue);
    }

    /**
     * 累加、累减.
     * @param string $hkey
     * @param float $hvalue
     * @param mixed $parentId
     */
    public function incr($parentId, $hkey, $hvalue)
    {
        if (! $this->exist($parentId)) {
            $this->initialize($parentId);
        }

        $key = $this->getCacheKey($parentId);

        return $this->redis()->hIncrByFloat($key, (string) $hkey, (float) $hvalue);
    }

    /**
     * 将多个元素插入到列表.
     * @param $score
     * @param $value
     * @param mixed $hashKeys
     * @param mixed $parentId
     * @return int
     */
    public function mset($parentId, $hashKeys)
    {
        if (! $this->exist($parentId)) {
            $this->initialize($parentId);
        }

        $key = $this->getCacheKey($parentId);

        return $this->redis()->hMset($key, $hashKeys);
    }

    /**
     * @param mixed $parentId
     * @param array $hashKeys
     * @throws Exceptions\CollectionException
     */
    public function mget($parentId, $hashKeys): array
    {
        if (! $this->exist($parentId)) {
            $hash = $this->initialize($parentId);
        } else {
            $hash = $this->redis()->hMGet($this->getCacheKey($parentId), $hashKeys);
        }

        $result = [];
        foreach ($hashKeys as $key) {
            $result[$key] = $hash[$key] ?? null;
        }

        return $result;
    }

    /**
     * 查询单个值
     * @param mixed $parentId
     * @param mixed $hashKey
     * @return string
     * @throws Exceptions\CollectionException
     */
    public function hget($parentId, $hashKey)
    {
        if (! $this->exist($parentId)) {
            $this->initialize($parentId);
        }

        $key = $this->getCacheKey($parentId);

        return $this->redis()->hGet($key, $hashKey);
    }

    /**
     * 删除key.
     * @param mixed ...$hashKey
     * @param mixed $parentId
     * @param mixed $hashKey1
     * @return false|int
     * @throws Exceptions\CollectionException
     */
    public function hdel($parentId, $hashKey1, ...$hashKey)
    {
        $key = $this->getCacheKey($parentId);
        return $this->redis()->hDel($key, $hashKey1, ...$hashKey);
    }

    /**
     * 删除hash.
     * @param $value
     * @param mixed $parentId
     * @return int
     */
    public function delete($parentId)
    {
        $key = $this->getCacheKey($parentId);

        return $this->redis()->del($key);
    }

    /**
     * 超时时间.
     * @param mixed $parentId
     * @return int
     */
    public function ttl($parentId)
    {
        if (! $this->exist($parentId)) {
            $this->initialize($parentId);
        }

        $key = $this->getCacheKey($parentId);

        return $this->redis()->ttl($key);
    }

    public function getTtl(): int
    {
        return $this->ttl;
    }

    /**
     * 是否需要初始化数据.
     */
    protected function isInitialize(): bool
    {
        return ! $this->exist;
    }
}
