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

use Redis;
use SwoftTest\Testing\DemoCollection;
use SwoftTest\Testing\DemoZSetCollection;
use SwoftTest\Testing\DemoZSetTTLCollection;

/**
 * @internal
 * @coversNothing
 */
class ZSetCollectionTest extends AbstractTestCase
{
    protected $pid = 1;

    protected function tearDown(): void
    {
        $redis = new Redis();
        $redis->connect('127.0.0.1');
        $redis->del('demo:1');
    }

    public function testAdd()
    {
        $collection = new DemoCollection();
        $collection->add($this->pid, 2, 3);

        $this->assertTrue($collection->redis()->exists('demo:1') > 0);
        $this->assertTrue($collection->redis()->zScore('demo:1', 3) == 2);

        $res = $collection->incr($this->pid, 1, 3);
        $this->assertSame(3.0, $res);
        $this->assertTrue($collection->redis()->zScore('demo:1', 3) == 3);
        $this->assertEquals(3, $collection->score($this->pid, 3));
    }

    public function testAddMore()
    {
        $collection = new DemoCollection();
        $collection->add($this->pid, 2, 3, 4, 'v4');

        $this->assertTrue($collection->redis()->exists('demo:1') > 0);
        $this->assertTrue($collection->redis()->zScore('demo:1', 3) == 2);
        $this->assertTrue($collection->redis()->zScore('demo:1', 'v4') == 4);

        $collection->incr($this->pid, 1, 3);
        $this->assertTrue($collection->redis()->zScore('demo:1', 3) == 3);
        $this->assertEquals(3, $collection->score($this->pid, 3));

        $collection->incr($this->pid, 1, 'v4');
        $this->assertTrue($collection->redis()->zScore('demo:1', 'v4') == 5);
        $this->assertEquals(5, $collection->score($this->pid, 'v4'));
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
        $collection->delete($this->pid);

        $res = $collection->all($this->pid);
        $this->assertEquals([1 => 0, 2 => 1], $res);
    }

    public function testPagination()
    {
        $collection = new DemoCollection();
        $collection->delete($this->pid);

        $collection->add($this->pid, 2, 3);
        $collection->add($this->pid, 3, 4);
        $collection->add($this->pid, 4, 5);

        [$count, $item] = $collection->pagination($this->pid, 0, 2);
        $this->assertEquals(5, $count);
        $this->assertEquals([5 => 4, 4 => 3], $item);

        $item = $collection->revRange($this->pid, 0, 2);
        $this->assertEquals([5 => 4, 4 => 3], $item);

        [$count, $item] = $collection->pagination($this->pid, 2, 2);
        $this->assertEquals(5, $count);
        $this->assertEquals([3 => 2, 2 => 1], $item);

        $item = $collection->revRange($this->pid, 2, 2);
        $this->assertEquals([3 => 2, 2 => 1], $item);
    }

    public function testZSetCount()
    {
        $collection = new DemoCollection();
        $collection->delete($this->pid);

        $count = $collection->count($this->pid);

        $this->assertEquals(2, $count);

        $count = $collection->count('123');

        $this->assertEquals(0, $count);
    }

    public function testZSetOnlyScore()
    {
        $collection = new DemoCollection();
        $collection->delete($this->pid);

        $res = $collection->score($this->pid, 'a');
        $this->assertFalse($res);

        $collection->delete($this->pid);
        $res = $collection->score($this->pid, 'a', false);
        $this->assertFalse($res);

        $collection->add($this->pid, 1, 'b');
        $res = $collection->score($this->pid, 'a', false);
        $this->assertFalse($res);

        $collection->add($this->pid, 1, 'a');
        $res = $collection->score($this->pid, 'a', false);
        $this->assertSame(1.0, $res);
    }

    public function testExistZSetAll()
    {
        $col = new DemoZSetCollection(false);
        $this->assertSame(2, $col->count(1));
        $this->assertEquals(['a' => 1, 'b' => 2], $col->all(1));

        $col = new DemoZSetCollection(true);
        $col->delete(2);
        $this->assertSame(0, $col->count(2));
        $this->assertEquals([], $col->all(2));

        $col->add(2, time(), 'a');
        $this->assertSame(1, $col->count(2));
        $this->assertEquals(['a' => time()], $col->all(2));
    }

    public function testMultipleZScore()
    {
        $collection = new DemoCollection();
        $collection->add($this->pid, 2, 3);

        $res = $collection->multipleZScore($this->pid, [2, 3]);
        $this->assertEquals([2 => '1', 3 => '2'], $res);

        $res = $collection->multipleZScore($this->pid, [2, 2 => 3]);
        $this->assertEquals([2 => '1', 3 => '2'], $res);

        $collection->delete($this->pid);
        $res = $collection->multipleZScore($this->pid, [2, 3]);
        $this->assertEquals([2 => '1'], $res);
    }

    public function testFreshTTL()
    {
        $foo = new DemoZSetTTLCollection();

        $foo->incr($this->pid, 1, 'id');

        $res = $foo->redis()->ttl('demo:1');

        $this->assertEquals(10, $res);

        $foo->freshTTL($this->pid, 20);

        $res = $foo->redis()->ttl('demo:1');

        $this->assertEquals(20, $res);
    }
}
