<?php
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://doc.swoft.org
 * @contact  limingxin@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */
namespace Xin\RedisCollection;

use Xin\RedisCollection\Exceptions\CollectionException;

abstract class ZSetCollection
{
    /**
     * redis key
     * @var string
     */
    protected $prefix;

    const DEFAULT_ID = 0;

    /**
     * 从DB中读取对应的全部列表
     * @author limx
     * @param $parentId
     * @return ['$id'=>'$score']
     */
    abstract public function reload($parentId);

    /**
     * 返回Redis实例
     * @author limx
     * @return \Redis
     */
    abstract public function redis();

    /**
     * Redis数据初始化
     * @author limx
     * @param $parentId
     */
    public function initialize($parentId)
    {
        $list = $this->reload($parentId);
        $params = [0, static::DEFAULT_ID];
        foreach ($list as $id => $score) {
            $params[] = $score;
            $params[] = $id;
        }

        $key = $this->getCacheKey($parentId);

        $this->redis()->zAdd($key, ...$params);

        return $list;
    }

    /**
     * 当前列表是否存在
     * @author limx
     * @param $parentId
     * @return mixed
     */
    public function exist($parentId)
    {
        $key = $this->getCacheKey($parentId);

        return $this->redis()->exists($key);
    }

    /**
     * 删除缓存
     * @author limx
     * @param $parentId
     * @return bool
     */
    public function delete($parentId)
    {
        $key = $this->getCacheKey($parentId);

        return $this->redis()->del($key);
    }

    /**
     * 查询所有数据
     * @author limx
     * @param $parentId
     * @return array
     */
    public function all($parentId)
    {
        $key = $this->getCacheKey($parentId);
        $res = $this->redis()->zRevRange($key, 0, -1, true);
        if (empty($res)) {
            return $this->initialize($parentId);
        }

        unset($res[static::DEFAULT_ID]);
        return $res;
    }

    /**
     * 将元素插入到列表
     * @author limx
     * @param $parentId
     * @param $score
     * @param $value
     * @return int
     */
    public function add($parentId, $score, $value)
    {
        if (!$this->exist($parentId)) {
            $this->initialize($parentId);
        }

        $key = $this->getCacheKey($parentId);

        return $this->redis()->zAdd($key, $score, $value);
    }

    /**
     * 获取元素分值
     * @author limx
     * @param $parentId
     * @param $value
     */
    public function score($parentId, $value)
    {
        if (!$this->exist($parentId)) {
            $this->initialize($parentId);
        }

        $key = $this->getCacheKey($parentId);

        return $this->redis()->zScore($key, $value);
    }

    /**
     * 将此元素从列表中移除
     * @author limx
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
     * 分页查询
     * @author limx
     * @param       $parentId
     * @return ['$count',['$id'=>'$score']]
     */
    public function pagination($parentId, $offset = 0, $limit = 10)
    {
        $key = $this->getCacheKey($parentId);

        $count = $this->count($parentId);

        $end = $offset + $limit - 1;
        $items = $this->redis()->zRevRange($key, $offset, $end, true);

        return [$count, $items];
    }

    /**
     * 返回有序集合总数
     * @author limx
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
            $count--;
        }

        return $count;
    }

    protected function getCacheKey($parentId)
    {
        if (empty($this->prefix)) {
            throw new CollectionException('The prefix is required!');
        }

        return $this->prefix . $parentId;
    }
}
