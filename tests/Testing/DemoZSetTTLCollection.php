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

namespace SwoftTest\Testing;

use Redis;
use Xin\RedisCollection\ZSetCollection;

class DemoZSetTTLCollection extends ZSetCollection
{
    protected $redis;

    protected $ttl = 10;

    protected $prefix = 'demo:';

    public function __construct()
    {
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1');
    }

    public function reload($parentId)
    {
        return [];
    }

    public function redis()
    {
        return $this->redis;
    }
}
