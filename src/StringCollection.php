<?php


namespace Xin\RedisCollection;


use Xin\RedisCollection\Exceptions\CollectionException;

abstract class StringCollection
{
    /**
     * redis key
     * @var string
     */
    protected $prefix;

    /**
     * 返回Redis实例
     * @author limx
     * @return \Redis
     */
    abstract public function redis();

    /**
     * 是否存在
     * @author limx
     * @param $id
     * @return bool
     */
    public function exist($id)
    {
        $key = $this->getCacheKey($id);

        return $this->redis()->exists($key);
    }

    /**
     * 获取数据
     * @author limx
     * @param $id
     * @return bool|string
     */
    public function get($id)
    {
        $key = $this->getCacheKey($id);

        return $this->redis()->get($key);
    }

    /**
     * 设置数据
     * @author limx
     * @param     $id
     * @param     $value 数据
     * @param int $ttl   超时时间 秒
     * @return bool
     */
    public function set($id, $value, $ttl = 3600)
    {
        $key = $this->getCacheKey($id);

        return $this->redis()->set($key, $value, $ttl);
    }

    /**
     * 删除数据
     * @author limx
     * @param $id
     */
    public function delete($id)
    {
        $key = $this->getCacheKey($id);

        return $this->redis()->delete($key);
    }

    protected function getCacheKey($id)
    {
        if (empty($this->prefix)) {
            throw new CollectionException('The prefix is required!');
        }

        return $this->prefix . $id;
    }
}