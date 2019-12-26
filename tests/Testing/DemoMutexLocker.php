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

namespace SwoftTest\Testing;

use Xin\RedisCollection\MutexLocker;

class DemoMutexLocker extends MutexLocker
{
    protected $redis;

    protected $prefix = 'demo:mutex:locker:';

    public function __construct()
    {
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1');
    }

    public function redis()
    {
        return $this->redis;
    }
}
