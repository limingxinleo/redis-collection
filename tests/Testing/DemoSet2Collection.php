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

use Xin\RedisCollection\SetCollection;

class DemoSet2Collection extends SetCollection
{
    protected $prefix = 'demoset2:';

    protected $exist = true;

    protected $redis;

    public function __construct()
    {
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1');
    }

    public function reload($parentId): array
    {
        return [];
    }

    public function redis()
    {
        return $this->redis;
    }
}
