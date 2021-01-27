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

abstract class HyperLogLogCounter
{
    use CacheKeyTrait;

    /**
     * redis key.
     * @var string
     */
    protected $prefix;

    /**
     * 是否认为当前ZSET一定存在.
     * @var bool
     */
    protected $exist = false;

    /**
     * 超时时间.
     * @var int
     */
    protected $ttl = 0;

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
        $sets = $this->reload($parentId);
        $key = $this->getCacheKey($parentId);
        $this->redis()->pfAdd($key, $sets);
        if (is_int($this->ttl) && $this->ttl > 0) {
            $this->redis()->expire($key, $this->ttl);
        }
    }

    public function add($parentId, array $ids)
    {
        if (! $this->check($parentId)) {
            $this->initialize($parentId);
        }

        $key = $this->getCacheKey($parentId);
        return $this->redis()->pfAdd($key, $ids);
    }

    public function count($parentId)
    {
        if (! $this->check($parentId)) {
            $this->initialize($parentId);
        }

        $key = $this->getCacheKey($parentId);
        return $this->redis()->pfCount($key);
    }

    public function clear($parentId)
    {
        $key = $this->getCacheKey($parentId);
        return $this->redis()->del($key);
    }

    /**
     * @param $parentId
     * @throws Exceptions\CollectionException
     * @return bool|int
     */
    public function ttl($parentId)
    {
        return $this->redis()->ttl($this->getCacheKey($parentId));
    }

    public function exist($id): bool
    {
        return (bool) $this->redis()->exists($this->getCacheKey($id));
    }

    /**
     * 判断当前Counter是否存在.
     * @param $parentId
     * @throws Exceptions\CollectionException
     * @return bool
     */
    protected function check($parentId)
    {
        if ($this->exist) {
            return true;
        }

        $key = $this->getCacheKey($parentId);

        if ($this->redis()->exists($key)) {
            return true;
        }

        return false;
    }
}
