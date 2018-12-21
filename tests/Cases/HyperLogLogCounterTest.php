<?php
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://doc.swoft.org
 * @contact  limingxin@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */
namespace SwoftTest\Cases;

use SwoftTest\Testing\DemoHyperLogLog2Counter;
use SwoftTest\Testing\DemoHyperLogLogCounter;

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
}
