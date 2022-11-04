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
use Xin\RedisCollection\MutexLocker;

class DemoMutexLockerDelFailed extends MutexLocker
{
    public $id = 0;

    protected $redis;

    protected $prefix = 'demo:mutex:locker:';

    public function __construct()
    {
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1');
    }

    public function redis()
    {
        return $this->redis;
    }

    public function del($id)
    {
    }

    protected function wait(int $ms): void
    {
        ++$this->id;
        parent::wait($ms);
    }
}
