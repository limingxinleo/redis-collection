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

    public function exist($parentId)
    {
        $key = $this->prefix . $parentId;

        return $this->redis()->has($key);
    }

    public function all($parentId)
    {
        $this->initialize($parentId);

        $key = $this->prefix . $parentId;
        return $this->redis()->zRevRange($key, 0, -1, true);
    }

    public function add($parentId, $score, $value)
    {
        $this->initialize($parentId);

        $key = $this->prefix . $parentId;

        return $this->redis()->zAdd($key, $score, $value);
    }

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
     * @param array $page
     * @return ['$count',['$id'=>'$score']]
     */
    public function pagination($parentId, $page = [])
    {
        $this->initialize($parentId);

        $offset = 0;
        $limit = 10;
        $key = $this->prefix . $parentId;

        $count = $this->redis()->zCard($key);

        if (isset($page['offset'])) {
            $offset = intval($page['offset']);
        }

        if (isset($page['limit'])) {
            $limit = intval($page['limit']);
        }

        $end = $offset + $limit - 1;
        $items = $this->redis()->zRevRange($key, $offset, $end, true);

        return [$count, $items];
    }
}
