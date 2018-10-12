<?php


namespace Xin\RedisCollection;


abstract class HashCollection
{
    /**
     * redis key
     * @var string
     */
    protected $prefix;

    /**
     * @var integer
     */
    protected $ttl = 0;

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
        if (!$this->exist($parentId)) {
            $hash = $this->reload($parentId);
            $key = $this->prefix . $parentId;

            $this->redis()->hMset($key, $hash);

            // 增加超时时间配置
            if (is_int($this->ttl) && $this->ttl > 0) {
                $this->redis()->expire($key, $this->ttl);
            }
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
    public function get($parentId)
    {
        $this->initialize($parentId);

        $key = $this->prefix . $parentId;
        return $this->redis()->hGetAll($key);
    }

    /**
     * 将元素插入到列表
     * @author limx
     * @param $parentId
     * @param $score
     * @param $value
     * @return int
     */
    public function set($parentId, $hkey, $hvalue)
    {
        $this->initialize($parentId);

        $key = $this->prefix . $parentId;

        return $this->redis()->hSet($key, $hkey, $hvalue);
    }

    /**
     * 将多个元素插入到列表
     * @author limx
     * @param $parentId
     * @param $score
     * @param $value
     * @return int
     */
    public function mset($parentId, $hashKeys)
    {
        $this->initialize($parentId);

        $key = $this->prefix . $parentId;

        return $this->redis()->hMset($key, $hashKeys);
    }

    /**
     * 删除hash
     * @author limx
     * @param $parentId
     * @param $value
     * @return int
     */
    public function delete($parentId)
    {
        $key = $this->prefix . $parentId;

        return $this->redis()->del($key);
    }

    /**
     * 超时时间
     * @author limx
     * @param $parentId
     * @return int
     */
    public function ttl($parentId)
    {
        $key = $this->prefix . $parentId;

        return $this->redis()->ttl($key);
    }
}