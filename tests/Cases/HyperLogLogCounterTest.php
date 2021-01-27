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

use SwoftTest\Testing\DemoHyperLogLog2Counter;
use SwoftTest\Testing\DemoHyperLogLogCounter;

/**
 * @internal
 * @coversNothing
 */
class HyperLogLogCounterTest extends AbstractTestCase
{
    public function testAdd()
    {
        $counter = new DemoHyperLogLogCounter();
        $counter->clear(1);
        $this->assertEquals(2, $counter->count(1));

        $bool = $counter->add(1, ['bv1']);
        $this->assertSame(1, $bool);
        $this->assertEquals(3, $counter->count(1));

        $bool = $counter->add(1, ['bv1']);
        $this->assertSame(0, $bool);
    }

    public function testExist()
    {
        $counter = new DemoHyperLogLog2Counter();
        $counter->clear(1);
        $this->assertEquals(0, $counter->count(1));

        $bool = $counter->add(1, ['bv1']);
        $this->assertSame(1, $bool);
        $this->assertEquals(1, $counter->count(1));
    }

    public function testReturn()
    {
        $counter = new DemoHyperLogLog2Counter();
        $counter->clear(1);

        $bool = $counter->add(1, ['bv1']);
        $this->assertSame(1, $bool);

        $bool = $counter->add(1, ['bv1']);
        $this->assertSame(0, $bool);

        $bool = $counter->add(1, ['bv2', 'bv3']);
        $this->assertSame(1, $bool);

        $bool = $counter->add(1, ['bv4', 'bv3']);
        $this->assertSame(1, $bool);

        $bool = $counter->add(1, ['bv4', 'bv3']);
        $this->assertSame(0, $bool);
    }

    public function testTime()
    {
        $key = 'hytime';
        $time = 2;
        $counter = new DemoHyperLogLog2Counter();
        $counter->clear($key);

        $counter->setExist(false);
        $counter->setTtl($time);

        $this->assertSame($counter->count($key), 2);

        $counter->add($key, ['bv4', 'bv3']);
        $this->assertSame(4, $counter->count($key));

        sleep($time);

        $this->assertSame(false, $counter->exist($key));
    }
}
