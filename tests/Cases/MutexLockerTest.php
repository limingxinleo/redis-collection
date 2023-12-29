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

namespace SwoftTest\Cases;

use SwoftTest\Testing\DemoMutexLocker;
use SwoftTest\Testing\DemoMutexLockerDelFailed;
use Throwable;
use Xin\RedisCollection\Exceptions\MutexLockerException;

/**
 * @internal
 * @coversNothing
 */
class MutexLockerTest extends AbstractTestCase
{
    protected function tearDown(): void
    {
        $locker = new DemoMutexLocker();
        $locker->del(1);
    }

    public function testTryMutexLocker()
    {
        $locker = new DemoMutexLocker();
        $uniqid = uniqid();
        $result = $locker->try(1, function () use ($uniqid) {
            return $uniqid;
        });

        $this->assertSame($result, $uniqid);
    }

    public function testLockMutexLocker()
    {
        $locker = new DemoMutexLocker();
        $id = uniqid();
        $result = $locker->lock($id, '1', 1);
        $this->assertTrue($result);
        $result = $locker->lock($id, '1', 1);
        $this->assertFalse($result);
        $locker->del($id);
        $result = $locker->lock($id, '1', 1);
        $this->assertTrue($result);
        $locker->del($id);
    }

    public function testTryMutexLockerFailed()
    {
        $locker = new DemoMutexLocker();
        $locker->redis()->set('demo:mutex:locker:1', '1', ['NX', 'EX' => 10]);
        $uniqid = uniqid();

        $this->expectException(MutexLockerException::class);
        $this->expectExceptionMessage('Try to get MutexLocker failed 5 times.');

        $locker->try(1, function () use ($uniqid) {
            $this->assertTrue(false);
            return $uniqid;
        }, 5);
    }

    public function testTryMutexLockerAgain()
    {
        $locker = new DemoMutexLocker();
        $locker->redis()->set('demo:mutex:locker:1', '1', ['NX', 'PX' => 50]);
        $uniqid = uniqid();

        $result = $locker->try(1, function () use ($uniqid) {
            return $uniqid;
        });

        $this->assertSame($result, $uniqid);
        $this->assertSame(1, $locker->id);
    }

    public function testTryMutexLockerFailedAgain()
    {
        $locker = new DemoMutexLocker();
        $locker->redis()->set('demo:mutex:locker:1', '1', ['NX', 'PX' => 50]);
        $uniqid = uniqid();

        try {
            $locker->try(1, function () use ($uniqid) {
                return $uniqid;
            }, 2, 10);
        } catch (Throwable $exception) {
        }

        $this->expectException(MutexLockerException::class);
        $this->expectExceptionMessage('Try to get MutexLocker failed 2 times.');

        $locker->try(1, function () use ($uniqid) {
            return $uniqid;
        }, 2, 10);
    }

    public function testTryMutexLockerDelFailed()
    {
        $locker = new DemoMutexLockerDelFailed();
        $uniqid = uniqid();

        $result = $locker->try(1, function () use ($uniqid) {
            return $uniqid;
        });

        $this->assertSame($result, $uniqid);
        $ttl = $locker->redis()->ttl('demo:mutex:locker:1');

        $this->assertEquals(2, $ttl);
    }
}
