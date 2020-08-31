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

abstract class SetCollection
{
    use CacheKeyTrait;

    const DEFAULT_ID = '0';

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
     * 从DB中读取对应的全部列表.
     * @param $parentId
     */
    abstract public function reload($parentId): array;

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
        $list = $this->reload($parentId);
        $params = $list;
        $params[] = static::DEFAULT_ID;

        $key = $this->getCacheKey($parentId);

        $this->redis()->sAdd($key, ...$params);
        if ($this->getTtl() > 0) {
            $this->redis()->expire($key, $this->getTtl());
        }

        return $list;
    }

    /**
     * 当前列表是否存在.
     * @param $parentId
     * @return mixed
     */
    public function exist($parentId)
    {
        if ($this->exist) {
            return true;
        }

        $key = $this->getCacheKey($parentId);

        return $this->redis()->exists($key);
    }

    /**
     * 删除缓存.
     * @param $parentId
     * @return bool
     */
    public function delete($parentId)
    {
        $key = $this->getCacheKey($parentId);

        return $this->redis()->del($key);
    }

    /**
     * 查询所有数据.
     * @param $parentId
     * @return array
     */
    public function all($parentId)
    {
        $key = $this->getCacheKey($parentId);
        $res = $this->redis()->sMembers($key);
        if (empty($res)) {
            // 如果默认 SET 一定存在，则不需要进行初始化
            if (! $this->exist) {
                return $this->initialize($parentId);
            }

            return [];
        }

        $key = array_search(static::DEFAULT_ID, $res);
        if ($key !== false) {
            unset($res[$key]);
        }
        return array_values($res);
    }

    /**
     * 删除默认值
     * @return array
     */
    public function deleteDefault(array $res)
    {
        $key = array_search(static::DEFAULT_ID, $res);
        if ($key !== false) {
            unset($res[$key]);
        }
        return $res;
    }

    /**
     * 将元素插入到列表.
     * @param $parentId
     * @param $value
     * @return int
     */
    public function add($parentId, $value)
    {
        if (! $this->exist($parentId)) {
            $this->initialize($parentId);
        }

        $key = $this->getCacheKey($parentId);

        if (is_array($value)) {
            return $this->redis()->sAdd($key, ...$value);
        }
        return $this->redis()->sAdd($key, $value);
    }

    /**
     * 获取元素分值
     * @param $parentId
     * @param $value
     */
    public function isMember($parentId, $value)
    {
        if (! $this->exist($parentId)) {
            $list = $this->initialize($parentId);
            return in_array($value, $list);
        }

        $key = $this->getCacheKey($parentId);

        return $this->redis()->sIsMember($key, $value);
    }

    /**
     * 将此元素从列表中移除.
     * @param $parentId
     * @param $value
     * @return int
     */
    public function rem($parentId, $value)
    {
        $key = $this->getCacheKey($parentId);

        return $this->redis()->sRem($key, $value);
    }

    /**
     * 返回有序集合总数.
     * @param $parentId
     */
    public function count($parentId)
    {
        $key = $this->getCacheKey($parentId);

        $count = $this->redis()->sCard($key);
        if ($this->exist) {
            return $count;
        }

        if ($count == 0) {
            $list = $this->initialize($parentId);
            $count = count($list);
        } else {
            --$count;
        }

        return $count;
    }

    public function getTtl(): int
    {
        return $this->ttl;
    }
}
