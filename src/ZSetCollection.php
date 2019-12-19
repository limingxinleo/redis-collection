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

abstract class ZSetCollection
{
    use CacheKeyTrait;

    const DEFAULT_ID = 0;

    const SORT_DESC = 'desc';

    const SORT_ASC = 'asc';

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
     * 是否认为当前ZSET一定存在，若为true则超时时间无效.
     * @var bool
     */
    protected $exist = false;

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
        $params = [0, static::DEFAULT_ID];
        foreach ($list as $id => $score) {
            $params[] = (float) $score;
            $params[] = $id;
        }

        $key = $this->getCacheKey($parentId);

        $this->redis()->zAdd($key, ...$params);
        // 增加超时时间
        $this->expire($parentId);

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
     * @param mixed $sort
     * @return array
     */
    public function all($parentId, $sort = self::SORT_DESC)
    {
        $key = $this->getCacheKey($parentId);
        if ($sort !== self::SORT_ASC) {
            $res = $this->redis()->zRevRange($key, 0, -1, true);
        } else {
            $res = $this->redis()->zRange($key, 0, -1, true);
        }

        if (empty($res)) {
            return $this->initialize($parentId);
        }

        unset($res[static::DEFAULT_ID]);
        return $res;
    }

    /**
     * 将元素插入到列表.
     * @param $parentId
     * @param $score
     * @param $value
     * @return int
     */
    public function add($parentId, $score, $value)
    {
        if (! $this->exist($parentId)) {
            $this->initialize($parentId);
        }

        $key = $this->getCacheKey($parentId);

        return $this->redis()->zAdd($key, $score, $value);
    }

    /**
     * @param $parentId
     * @param $score
     * @param $value
     * @return int
     */
    public function incr($parentId, $score, $value)
    {
        if (! $this->exist($parentId)) {
            $this->initialize($parentId);
        }

        $key = $this->getCacheKey($parentId);

        return $this->redis()->zIncrBy($key, $score, $value);
    }

    /**
     * 获取元素分值
     * @param $parentId
     * @param $value
     */
    public function score($parentId, $value)
    {
        if (! $this->exist($parentId)) {
            $this->initialize($parentId);
        }

        $key = $this->getCacheKey($parentId);

        return $this->redis()->zScore($key, $value);
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

        return $this->redis()->zRem($key, $value);
    }

    /**
     * 分页查询.
     * @param $parentId
     * @param mixed $offset
     * @param mixed $limit
     * @return ['$count',['$id'=>'$score']]
     */
    public function pagination($parentId, $offset = 0, $limit = 10)
    {
        $key = $this->getCacheKey($parentId);

        $count = $this->count($parentId);

        $end = $offset + $limit - 1;
        $items = $this->redis()->zRevRange($key, (int) $offset, (int) $end, true);
        unset($items[static::DEFAULT_ID]);
        return [$count, $items];
    }

    /**
     * 返回有序集合总数.
     * @param $parentId
     */
    public function count($parentId)
    {
        $key = $this->getCacheKey($parentId);

        $count = $this->redis()->zCard($key);
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
