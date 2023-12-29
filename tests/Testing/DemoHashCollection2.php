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
use Xin\RedisCollection\HashCollection;

class DemoHashCollection2 extends HashCollection
{
    protected $prefix = 'demohash2:';

    protected $ttl = 3600;

    protected $redis;

    public function __construct()
    {
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1');
    }

    public function reload($parentId)
    {
        return [
            'id' => 1,
            'name' => 'limx',
        ];
    }

    public function redis()
    {
        return $this->redis;
    }
}
