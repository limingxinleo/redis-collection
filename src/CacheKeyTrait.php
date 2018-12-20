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

trait CacheKeyTrait
{
    protected function getCacheKey($parentId)
    {
        if (empty($this->prefix)) {
            throw new CollectionException('The prefix is required!');
        }

        return $this->prefix . $parentId;
    }
}
