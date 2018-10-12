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
use SwoftTest\Testing\DemoHashCollection2;

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
        ], $collection->redis()->hGetAll('demohash:1'));
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
        ], $collection->redis()->hGetAll('demohash:1'));
    }

    public function testGet()
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
        $res = $collection->ttl($this->pid);

        $this->assertEquals(-2, $res);

        $collection = new DemoHashCollection2();
        $collection->get($this->pid);
        $res = $collection->ttl($this->pid);

        $this->assertTrue($res > 0);
    }
}
