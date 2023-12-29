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

use Xin\RedisCollection\Exceptions\CollectionException;

trait CacheKeyTrait
{
    public function freshTTL($parentId, int $ttl = 86400): void
    {
        $key = $this->getCacheKey($parentId);

        $this->redis()->expire($key, $ttl);
    }

    protected function getCacheKey($parentId): string
    {
        if (empty($this->prefix)) {
            throw new CollectionException('The prefix is required!');
        }

        return $this->prefix . $parentId;
    }
}
