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

use Xin\RedisCollection\Exceptions\CollectionException;

abstract class HashCollection
{
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
     * 返回Redis实例.
     * @return \Redis
     */
    abstract public function redis();

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
            if (is_int($this->getTtl()) && $this->getTtl() > 0) {
                $this->redis()->expire($key, $this->getTtl());
            }
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
     * @param mixed $hkey
     * @param mixed $hvalue
     * @return int
     */
    public function set($parentId, $hkey, $hvalue)
    {
        if (! $this->exist($parentId)) {
            $this->initialize($parentId);
        }

        $key = $this->getCacheKey($parentId);

        return $this->redis()->hSet($key, $hkey, $hvalue);
    }

    /**
     * 累加、累减.
     * @param $parentId
     * @param $hkey
     * @param $hvalue
     */
    public function incr($parentId, $hkey, $hvalue)
    {
        if (! $this->exist($parentId)) {
            $this->initialize($parentId);
        }

        $key = $this->getCacheKey($parentId);

        return $this->redis()->hIncrByFloat($key, $hkey, $hvalue);
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

        return $this->redis()->hMset($key, $hashKeys);
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
     * @return int
     */
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

    protected function getCacheKey($parentId): string
    {
        if (empty($this->prefix)) {
            throw new CollectionException('The prefix is required!');
        }

        return $this->prefix . $parentId;
    }
}
