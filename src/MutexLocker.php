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

namespace Xin\RedisCollection;

use Xin\RedisCollection\Exceptions\MutexLockerException;

abstract class MutexLocker
{
    use CacheKeyTrait;

    public $prefix = '';

    /**
     * @var int 锁超时时间
     */
    public $lockTime = 2;

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
        $key = $this->getCacheKey($id);
        $result = null;

        try {
            $runFirst = true;
            beginning:
            if ($this->redis()->set($key, '1', ['NX', 'EX' => $this->lockTime]) !== true) {
                if (--$times > 0) {
                    $runFirst = false;
                    goto beginning;
                }

                throw new MutexLockerException('Try to get MutexLocker failed.');
            }

            if ($runFirst || $runAgain) {
                $result = $closure();
            }
        } finally {
            $this->redis()->del($key);
        }

        return $result;
    }
}
