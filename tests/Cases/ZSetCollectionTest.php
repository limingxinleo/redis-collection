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

use SwoftTest\Testing\DemoCollection;

class ZSetCollectionTest extends AbstractTestCase
{
    protected $pid = 1;

    public function testAdd()
    {
        $collection = new DemoCollection();
        $collection->add($this->pid, 2, 3);

        $this->assertTrue($collection->redis()->exists('demo:1') > 0);
        $this->assertTrue($collection->redis()->zScore('demo:1', 3) == 2);
    }

    public function testScore()
    {
        $collection = new DemoCollection();
        $collection->add($this->pid, 2, 3);
        $res = $collection->score($this->pid, 3);
        $this->assertEquals(2, $res);

        $res = $collection->score($this->pid, 100);
        $this->assertTrue(empty($res));
    }

    public function testRem()
    {
        $collection = new DemoCollection();
        $collection->add($this->pid, 10, 3);

        $this->assertTrue($collection->redis()->zScore('demo:1', 3) == 10);

        $collection->rem($this->pid, 3);
        $this->assertTrue($collection->redis()->zScore('demo:1', 3) == false);
    }

    public function testAll()
    {
        $collection = new DemoCollection();
        $collection->redis()->del('demo:1');

        $res = $collection->all($this->pid);
        $this->assertEquals([1 => 0, 2 => 1], $res);
    }

    public function testPagination()
    {
        $collection = new DemoCollection();
        $collection->redis()->del('demo:1');

        list($count, $item) = $collection->pagination($this->pid, 0, 1);
        $this->assertEquals(2, $count);
        $this->assertEquals([2 => 1], $item);
    }

    public function testZSetCount()
    {
        $collection = new DemoCollection();
        $collection->redis()->del('demo:1');

        $count = $collection->count($this->pid);

        $this->assertEquals(2, $count);

        $count = $collection->count('123');

        $this->assertEquals(0, $count);
    }
}
