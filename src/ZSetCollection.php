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

use Xin\RedisCollection\Lua\HasLuaScript;
use Xin\RedisCollection\Lua\MultipleZScoreScript;

abstract class ZSetCollection
{
    use CacheKeyTrait;
    use HasLuaScript;

    public const DEFAULT_ID = 0;

    public const SORT_DESC = 'desc';

    public const SORT_ASC = 'asc';

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
     * 从DB中读取对应的全部列表.
     * @param $parentId
     * @return ['$id'=>'$score']
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
        $list = $this->reload($parentId);
        $params = [0, static::DEFAULT_ID];
        foreach ($list as $id => $score) {
            $params[] = (float) $score;
            $params[] = $id;
        }

        $key = $this->getCacheKey($parentId);

        $this->redis()->zAdd($key, ...$params);
        // 增加超时时间
        if (is_int($this->getTtl()) && $this->getTtl() > 0) {
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
            if ($this->exist) {
                // 如果默认一定存在，则直接返回数据
                return [];
            }
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
    public function add($parentId, ...$arguments)
    {
        if (! $this->exist($parentId)) {
            $this->initialize($parentId);
        }

        $key = $this->getCacheKey($parentId);

        return $this->redis()->zAdd($key, ...$arguments);
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
     * @param int|string $parentId
     * @param string $value
     * @param bool $initialize 值为 false 时，则不需要判断是否存在，不存在则重建缓存。(减少一次 Redis 指令，默认 key 一定存在)
     * @return bool|float KEY不存在 或 元素不存在 时返回 false
     */
    public function score($parentId, $value, bool $initialize = true)
    {
        if ($initialize && ! $this->exist($parentId)) {
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
     * @param string $parentId
     * @param int $offset
     * @param int $limit
     * @return array<string, float> [$id => $score]
     */
    public function revRange($parentId, $offset = 0, $limit = 10)
    {
        $key = $this->getCacheKey($parentId);

        $end = $offset + $limit - 1;
        $items = $this->redis()->zRevRange($key, (int) $offset, (int) $end, true);
        unset($items[static::DEFAULT_ID]);
        return $items;
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
            if (! $this->exist) {
                $list = $this->initialize($parentId);
                $count = count($list);
            }
        } else {
            if (! $this->exist) {
                --$count;
            }
        }

        return $count;
    }

    public function getTtl(): int
    {
        return $this->ttl;
    }

    /**
     * 批量获取 成员的分数.
     * @deprecated
     * @param $parentId
     * @throws Exceptions\CollectionException
     * @return array
     */
    public function zscores($parentId, array $members)
    {
        array_unshift($members, $this->getCacheKey($parentId));
        $data = $this->redis()->eval($this->getZscoresScript(), $members, 1);
        array_shift($members);
        return $this->parseResponse($members, $data);
    }

    /**
     * 批量获取 成员的分数.
     * @param int|string|\Stringable $parentId
     * @throws Exceptions\CollectionException
     * @return array
     */
    public function multipleZScore($parentId, array $members, bool $initialize = true)
    {
        if ($initialize && ! $this->exist($parentId)) {
            $this->initialize($parentId);
        }

        $script = new MultipleZScoreScript($this->getCacheKey($parentId), $members);
        return $this->runScript($script);
    }

    /**
     * 获取成员分数的脚本.
     */
    protected function getZscoresScript(): string
    {
        return <<<'LUA'
    local values = {};
    for i,v in ipairs(ARGV) do 
        values[i] = redis.call('zscore',KEYS[1],v);
    end
    return values;
LUA;
    }

    /**
     * 格式化成员函数的分值的结果，不存在的成员不返回.
     * @param $inputs
     * @param $result
     * @return array
     */
    protected function parseResponse($inputs, $result)
    {
        $return = [];
        foreach ($inputs as $i => $input) {
            if ($value = $result[$i] ?? false and $value !== false) {
                $return[$input] = $value;
            }
        }
        return $return;
    }
}
