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

abstract class HyperLogLogCollection
{
    use CacheKeyTrait;

    const DEFAULT_ID = 0;

    /**
     * redis key.
     * @var string
     */
    protected $prefix;

    /**
     * 超时时间.
     * @var int
     */
    protected $ttl = 0;

    /**
     * 是否认为当前SET一定存在，若为true则超时时间无效.
     * @var bool
     */
    protected $exist = false;

    /**
     * 返回Redis实例.
     * @return \Redis
     */
    abstract public function redis();

    abstract public function reload($parentId): array;

    abstract public function prefix(): string;

    public function getCacheKey($parentId): string
    {
        return $this->prefix() . $parentId;
    }

    public function initialize($parentId)
    {
        if (! $this->exist) {
            $list = $this->reload($parentId);
            $list[] = self::DEFAULT_ID;

            $cacheKey = $this->getCacheKey($parentId);
            $this->redis()->pfAdd($cacheKey, $list);
            if ($ttl = $this->getTtl() and $ttl > 0) {
                $this->redis()->expire($cacheKey, $ttl);
            }
        }
    }

    /**
     * 是否存在.
     * @param $id
     * @return bool
     */
    public function exist($id)
    {
        $cacheKey = $this->getCacheKey($id);
        return $this->redis()->exists($cacheKey);
    }

    /**
     * 设置数据.
     * @param $id
     * @param array $value 数据
     * @return bool
     */
    public function add($id, array $value)
    {
        $this->initialize($id);
        $cacheKey = $this->getCacheKey($id);
        return $this->redis()->pfAdd($cacheKey, $value);
    }

    /**
     * @param $id
     */
    public function count($id): int
    {
        $cacheKey = $this->getCacheKey($id);
        return $this->redis()->pfCount($cacheKey);
    }

    /**
     * 删除数据.
     * @param $id
     */
    public function delete($id)
    {
        $cacheKey = $this->getCacheKey($id);
        return $this->redis()->del($cacheKey);
    }

    /**
     * 获取字符串剩余时间.
     * @param $id
     */
    public function ttl($id)
    {
        $cacheKey = $this->getCacheKey($id);
        return $this->redis()->ttl($cacheKey);
    }

    public function getTtl(): int
    {
        return $this->ttl;
    }
}
