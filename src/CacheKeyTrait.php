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

use Xin\RedisCollection\Exceptions\CollectionException;

trait CacheKeyTrait
{
    protected $ttl = 0;

    /**
     * 返回Redis实例.
     * @return \Redis
     */
    abstract public function redis();

    public function getTtl(): int
    {
        return $this->ttl;
    }

    protected function getCacheKey($parentId): string
    {
        if (empty($this->prefix)) {
            throw new CollectionException('The prefix is required!');
        }

        return $this->prefix . $parentId;
    }

    protected function expire($parentId)
    {
        if ($this->getTtl() && intval($this->getTtl()) > 0 && intval($this->redis()->ttl($this->getCacheKey($parentId))) <= 0) {
            $this->redis()->expire($this->getCacheKey($parentId), (int) $this->getTtl());
        }
    }
}
