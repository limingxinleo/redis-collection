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

abstract class StringCollection
{
    use CacheKeyTrait;

    /**
     * redis key.
     * @var string
     */
    protected $prefix;

    /**
     * 返回Redis实例.
     * @return \Redis
     */
    abstract public function redis();

    /**
     * 是否存在.
     * @param $id
     * @return bool
     */
    public function exist($id)
    {
        $key = $this->getCacheKey($id);

        return $this->redis()->exists($key);
    }

    /**
     * 获取数据.
     * @param $id
     * @return bool|string
     */
    public function get($id)
    {
        $key = $this->getCacheKey($id);

        return $this->redis()->get($key);
    }

    /**
     * 设置数据.
     * @param $id
     * @param $value 数据
     * @param int $ttl 超时时间 秒
     * @return bool
     */
    public function set($id, $value, $ttl = 3600)
    {
        $key = $this->getCacheKey($id);
        if (! is_null($ttl)) {
            return $this->redis()->set($key, $value, $ttl);
        }
        return $this->redis()->set($key, $value);
    }

    /**
     * 删除数据.
     * @param $id
     */
    public function delete($id)
    {
        $key = $this->getCacheKey($id);

        return $this->redis()->delete($key);
    }

    /**
     * 获取字符串剩余时间.
     * @param $id
     */
    public function ttl($id)
    {
        $key = $this->getCacheKey($id);

        return $this->redis()->ttl($key);
    }
}
