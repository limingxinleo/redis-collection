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

abstract class HyperLogLogCounter
{
    use CacheKeyTrait;

    /**
     * redis key
     * @var string
     */
    protected $prefix;

    /**
     * 是否认为当前ZSET一定存在
     * @var bool
     */
    protected $exist = false;

    /**
     * 从DB中读取对应的全部列表
     * @author limx
     * @param $parentId
     * @return array
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
        $sets = $this->reload($parentId);
        $key = $this->getCacheKey($parentId);
        $this->redis()->pfAdd($key, $sets);
    }

    /**
     * 判断当前Counter是否存在
     * @param $parentId
     * @return bool
     * @throws Exceptions\CollectionException
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

    public function add($parentId, array $ids)
    {
        if (!$this->check($parentId)) {
            $this->initialize($parentId);
        }

        $key = $this->getCacheKey($parentId);
        return $this->redis()->pfAdd($key, $ids);
    }

    public function count($parentId)
    {
        if (!$this->check($parentId)) {
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
}
