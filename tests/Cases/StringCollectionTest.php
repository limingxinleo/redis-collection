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
use SwoftTest\Testing\DemoHashCollection;
use SwoftTest\Testing\DemoStringCollection;

class StringCollectionTest extends AbstractTestCase
{
    protected $pid = 1;

    public function testSet()
    {
        $collection = new DemoStringCollection();
        $collection->redis()->del('demostring:1');
        $collection->set($this->pid, 'xxxxx', 18);

        $this->assertTrue($collection->redis()->exists('demostring:1') > 0);
        $this->assertEquals('xxxxx', $collection->redis()->get('demostring:1'));
    }

    public function testGet()
    {
        $collection = new DemoStringCollection();
        $collection->redis()->del('demostring:1');
        $collection->set($this->pid, 'xxxxx', 18);

        $this->assertTrue($collection->redis()->exists('demostring:1') > 0);
        $this->assertEquals('xxxxx', $collection->get($this->pid));
    }

    public function testDelete()
    {
        $collection = new DemoStringCollection();
        $collection->redis()->del('demostring:1');
        $collection->set($this->pid, 'xxxxx', 18);

        $this->assertTrue($collection->redis()->exists('demostring:1') > 0);
        $this->assertTrue($collection->exist($this->pid) == true);
        $collection->delete($this->pid);
        $this->assertTrue($collection->redis()->exists('demostring:1') == false);
        $this->assertFalse($collection->exist($this->pid) == true);
    }
}
