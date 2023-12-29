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

class DemoHyperLogLogCounter extends HyperLogLogCounter
{
    protected $prefix = 'demohyper:';

    protected $redis;

    public function __construct()
    {
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1');
    }

    public function reload($parentId)
    {
        return [
            'a1', 'a2',
        ];
    }

    public function redis()
    {
        return $this->redis;
    }
}
