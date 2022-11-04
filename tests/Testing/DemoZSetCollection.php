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

class DemoZSetCollection extends ZSetCollection
{
    protected $prefix = 'demo:zset:';

    protected $redis;

    protected $exist = true;

    public function __construct(bool $exist)
    {
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1');
        $this->exist = $exist;
    }

    public function reload($parentId)
    {
        return ['a' => 1, 'b' => 2];
    }

    public function redis()
    {
        return $this->redis;
    }
}
