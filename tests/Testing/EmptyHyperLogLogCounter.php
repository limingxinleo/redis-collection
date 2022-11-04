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
use Xin\RedisCollection\HyperLogLogCounter;

class EmptyHyperLogLogCounter extends HyperLogLogCounter
{
    protected $prefix = 'emptyhyper:';

    protected $redis;

    protected $ttl = 60;

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

    public function setTtl(int $time)
    {
        $this->ttl = $time;
    }

    public function setExist(bool $value)
    {
        $this->exist = $value;
    }
}
