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

        $counter->add(1, ['bv1']);
        $this->assertEquals(3, $counter->count(1));
    }

    public function testExist()
    {
        $counter = new DemoHyperLogLog2Counter();
        $counter->clear(1);
        $this->assertEquals(0, $counter->count(1));

        $counter->add(1, ['bv1']);
        $this->assertEquals(1, $counter->count(1));
    }
}
