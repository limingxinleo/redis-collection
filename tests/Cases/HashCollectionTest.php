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

use SwoftTest\Testing\DemoHashCollection;
use SwoftTest\Testing\DemoHashCollection2;
use SwoftTest\Testing\DemoHashCollection3;

class HashCollectionTest extends AbstractTestCase
{
    protected $pid = 1;

    public function testSet()
    {
        $collection = new DemoHashCollection();
        $collection->redis()->del('demohash:1');
        $collection->set($this->pid, 'age', 18);

        $this->assertTrue($collection->redis()->exists('demohash:1') > 0);
        $this->assertEquals([
            'id' => 1,
            'name' => 'limx',
            'age' => 18,
        ], $collection->get($this->pid));

        $this->assertEquals([
            'id' => 1,
            'name' => 'limx',
            'age' => 18,
        ], $collection->get($this->pid));
    }

    public function testMSet()
    {
        $collection = new DemoHashCollection();
        $collection->redis()->del('demohash:1');
        $collection->mset($this->pid, ['age' => 18, 'sex' => 1]);

        $this->assertTrue($collection->redis()->exists('demohash:1') > 0);
        $this->assertEquals([
            'id' => 1,
            'name' => 'limx',
            'age' => 18,
            'sex' => 1,
        ], $collection->get($this->pid));

        $this->assertEquals([
            'id' => 1,
            'name' => 'limx',
            'age' => 18,
            'sex' => 1,
        ], $collection->get($this->pid));
    }

    public function testGet()
    {
        $collection = new DemoHashCollection();
        $collection->delete($this->pid);
        $collection->mset($this->pid, ['age' => 18, 'sex' => 1]);

        $this->assertTrue($collection->redis()->exists('demohash:1') > 0);
        $this->assertEquals([
            'id' => 1,
            'name' => 'limx',
            'age' => 18,
            'sex' => 1,
        ], $collection->get($this->pid));
    }

    public function testDelete()
    {
        $collection = new DemoHashCollection();
        $collection->mset($this->pid, ['age' => 18, 'sex' => 1]);

        $this->assertTrue($collection->redis()->exists('demohash:1') > 0);
        $collection->delete($this->pid);
        $this->assertTrue($collection->redis()->exists('demohash:1') == 0);
    }

    public function testTtl()
    {
        $collection = new DemoHashCollection();
        $collection->get($this->pid);
        $res = $collection->ttl($this->pid);

        $this->assertEquals(-1, $res);

        $collection = new DemoHashCollection2();
        $collection->get($this->pid);
        $res = $collection->ttl($this->pid);

        $this->assertTrue($res > 0);
    }

    public function testHashExist()
    {
        $collection = new DemoHashCollection();
        $res = $collection->get(2);

        $this->assertEquals([
            'id' => 1,
            'name' => 'limx'
        ], $res);
        $this->assertTrue($collection->exist(2) == 1);

        $collection = new DemoHashCollection3();
        $res = $collection->get(2);
        $this->assertTrue(empty($res));
        $this->assertFalse($collection->exist(2) == 1);
    }

    public function testHIncr()
    {
        $collection = new DemoHashCollection();

        $array = $collection->get($this->pid);
        $this->assertEquals(['id' => 1, 'name' => 'limx'], $array);

        $res = $collection->incr($this->pid, 'id', 1);
        $this->assertEquals(2, $res);

        $array = $collection->get($this->pid);
        $this->assertEquals(['id' => 2, 'name' => 'limx'], $array);

        $res = $collection->incr($this->pid, 'id', -1);
        $this->assertEquals(1, $res);

        $array = $collection->get($this->pid);
        $this->assertEquals(['id' => 1, 'name' => 'limx'], $array);

        $collection->delete($this->pid);
    }
}
