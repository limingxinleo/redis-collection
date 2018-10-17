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

abstract class ZSetCollection
{
    /**
     * redis key
     * @var string
     */
    protected $prefix;

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
        if (!$this->exist($parentId)) {
            $list = $this->reload($parentId);
            $params = [];
            foreach ($list as $id => $score) {
                $params[] = $score;
                $params[] = $id;
            }

            $key = $this->prefix . $parentId;

            $this->redis()->zAdd($key, ...$params);
        }
    }

    /**
     * 当前列表是否存在
     * @author limx
     * @param $parentId
     * @return mixed
     */
    public function exist($parentId)
    {
        $key = $this->prefix . $parentId;

        return $this->redis()->exists($key);
    }

    /**
     * 查询所有数据
     * @author limx
     * @param $parentId
     * @return array
     */
    public function all($parentId)
    {
        $this->initialize($parentId);

        $key = $this->prefix . $parentId;
        return $this->redis()->zRevRange($key, 0, -1, true);
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
        $this->initialize($parentId);

        $key = $this->prefix . $parentId;

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
        $this->initialize($parentId);

        $key = $this->prefix . $parentId;

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
        $this->initialize($parentId);

        $key = $this->prefix . $parentId;

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
        $this->initialize($parentId);

        $key = $this->prefix . $parentId;

        $count = $this->redis()->zCard($key);

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
        $this->initialize($parentId);

        $key = $this->prefix . $parentId;

        return $this->redis()->zCard($key) ?? 0;
    }
}
