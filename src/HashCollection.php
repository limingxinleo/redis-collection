<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Xin\RedisCollection;

abstract class HashCollection
{
    use CacheKeyTrait;

    const DEFAULT_KEY = 'swoft:none';

    const DEFAULT_VALUE = 'none';

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
     * @param $parentId
     * @return array
     */
    abstract public function reload($parentId);

    /**
     * Redis数据初始化.
     * @param $parentId
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
            $this->expire($parentId);
        }

        return $hash;
    }

    /**
     * 当前列表是否存在.
     * @param $parentId
     * @return mixed
     */
    public function exist($parentId)
    {
        $key = $this->getCacheKey($parentId);

        return $this->redis()->exists($key);
    }

    /**
     * 查询所有数据.
     * @param $parentId
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
     * @param $parentId
     * @param $score
     * @param $value
     * @param string $hkey
     * @param string $hvalue
     * @return int
     */
    public function set($parentId, $hkey, $hvalue)
    {
        if (! $this->exist($parentId)) {
            $this->initialize($parentId);
        }

        $key = $this->getCacheKey($parentId);

        $return = $this->redis()->hSet($key, (string) $hkey, $hvalue);
        // 增加超时时间配置
        $this->expire($parentId);
        return  $return;
    }

    /**
     * 累加、累减.
     * @param $parentId
     * @param string $hkey
     * @param float $hvalue
     */
    public function incr($parentId, $hkey, $hvalue)
    {
        if (! $this->exist($parentId)) {
            $this->initialize($parentId);
        }

        $key = $this->getCacheKey($parentId);

        $return = $this->redis()->hIncrByFloat($key, (string) $hkey, (float) $hvalue);
        // 增加超时时间配置
        $this->expire($parentId);
        return $return;
    }

    /**
     * 将多个元素插入到列表.
     * @param $parentId
     * @param $score
     * @param $value
     * @param mixed $hashKeys
     * @return int
     */
    public function mset($parentId, $hashKeys)
    {
        if (! $this->exist($parentId)) {
            $this->initialize($parentId);
        }

        $key = $this->getCacheKey($parentId);

        $return = $this->redis()->hMset($key, $hashKeys);
        // 增加超时时间配置
        $this->expire($parentId);
        return $return;
    }

    /**
     * 查询单个值
     * @param $parentId
     * @param $hashKey
     * @throws Exceptions\CollectionException
     * @return string
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
     * @param $parentId
     * @param $hashKey1
     * @param mixed ...$hashKey
     * @throws Exceptions\CollectionException
     * @return false|int
     */
    public function hdel($parentId, $hashKey1, ...$hashKey)
    {
        $key = $this->getCacheKey($parentId);
        return $this->redis()->hDel($key, $hashKey1, ...$hashKey);
    }

    /**
     * 删除hash.
     * @param $parentId
     * @param $value
     * @return int
     */
    public function delete($parentId)
    {
        $key = $this->getCacheKey($parentId);

        return $this->redis()->del($key);
    }

    /**
     * 超时时间.
     * @param $parentId
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

    /**
     * 是否需要初始化数据.
     */
    protected function isInitialize(): bool
    {
        return ! $this->exist;
    }
}
