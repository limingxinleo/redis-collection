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

use SwoftTest\Testing\DemoStringCollection;

/**
 * @internal
 * @coversNothing
 */
class StringCollectionTest extends AbstractTestCase
{
    protected $pid = 1;

    protected function tearDown(): void
    {
        $collection = new DemoStringCollection();
        $collection->delete($this->pid);
    }

    public function testSet()
    {
        $collection = new DemoStringCollection();
        $collection->redis()->del('demostring:1');
        $collection->set($this->pid, 'xxxxx', 18);

        $this->assertTrue($collection->redis()->exists('demostring:1') > 0);
        $this->assertEquals('xxxxx', $collection->redis()->get('demostring:1'));
    }

    public function testSetNotString()
    {
        $collection = new DemoStringCollection();
        $collection->set($this->pid, 1111, 18);

        $this->assertTrue($collection->redis()->exists('demostring:1') > 0);
        $this->assertEquals(1111, $collection->redis()->get('demostring:1'));
    }

    public function testGet()
    {
        $collection = new DemoStringCollection();
        $collection->redis()->del('demostring:1');
        $collection->set($this->pid, 'xxxxx', 18);

        $this->assertTrue($collection->redis()->exists('demostring:1') > 0);
        $this->assertEquals('xxxxx', $collection->get($this->pid));
        $this->assertEquals(18, $collection->ttl($this->pid));
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
