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

use Xin\RedisCollection\Exceptions\MutexLockerException;

abstract class MutexLocker
{
    use CacheKeyTrait;

    protected $prefix = '';

    /**
     * @var float 锁超时时间
     */
    protected $lockTime = 2;

    /**
     * 返回Redis实例.
     * @return \Redis
     */
    abstract public function redis();

    /**
     * 尝试获取锁 并执行对应操作.
     * @param int|string $id 锁ID
     * @param \Closure $closure 获取锁后需要执行的代码
     * @param int $times 尝试获取次数 <= 1 和 1 一致
     * @param int $ms 获取失败后的等待时间 毫秒
     * @param bool $runAgain 当首次争抢锁失败后，是否执行代码
     */
    public function try($id, \Closure $closure, int $times = 2, int $ms = 100, $runAgain = true)
    {
        $result = null;

        $runFirst = true;
        $tryCount = 0;

        beginning:
        $tryCount++;
        if ($this->redis()->set($this->getCacheKey($id), '1', ['NX', 'PX' => $this->lockTime * 1000]) !== true) {
            if ($tryCount < $times) {
                $runFirst = false;
                $this->wait($ms);
                goto beginning;
            }

            throw new MutexLockerException(sprintf('Try to get MutexLocker failed %s times.', $tryCount));
        }

        if ($runFirst || $runAgain) {
            try {
                $result = $closure();
            } finally {
                $this->del($id);
            }
        }

        return $result;
    }

    /**
     * 主动删除锁
     * @param int|string $id
     */
    public function del($id)
    {
        return $this->redis()->del($this->getCacheKey($id));
    }

    protected function wait(int $ms): void
    {
        if ($ms > 0) {
            usleep($ms * 1000);
        }
    }
}
