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

abstract class SetCollection
{
    /**
     * redis key
     * @var string
     */
    protected $prefix;

    /**
     * 是否认为当前SET一定存在
     * @var bool
     */
    protected $exist = false;

    const DEFAULT_ID = '0';

    /**
     * 从DB中读取对应的全部列表
     * @author limx
     * @param $parentId
     * @return array
     */
    abstract public function reload($parentId): array;

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
        $params = $list;
        $params[] = static::DEFAULT_ID;

        $key = $this->getCacheKey($parentId);

        $this->redis()->sAdd($key, ...$params);

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
        if ($this->exist) {
            return true;
        }

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
        $res = $this->redis()->sMembers($key);
        if (empty($res)) {
            return $this->initialize($parentId);
        }

        $key = array_search(static::DEFAULT_ID, $res);
        if ($key !== false) {
            unset($res[$key]);
        }
        return array_values($res);
    }

    /**
     * 将元素插入到列表
     * @author limx
     * @param $parentId
     * @param $value
     * @return int
     */
    public function add($parentId, $value)
    {
        if (!$this->exist($parentId)) {
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
     * @author limx
     * @param $parentId
     * @param $value
     */
    public function isMember($parentId, $value)
    {
        if (!$this->exist($parentId)) {
            $list = $this->initialize($parentId);
            return in_array($value, $list);
        }

        $key = $this->getCacheKey($parentId);

        return $this->redis()->sIsMember($key, $value);
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

        return $this->redis()->sRem($key, $value);
    }

    /**
     * 返回有序集合总数
     * @author limx
     * @param $parentId
     */
    public function count($parentId)
    {
        $key = $this->getCacheKey($parentId);

        $count = $this->redis()->sCard($key);
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
