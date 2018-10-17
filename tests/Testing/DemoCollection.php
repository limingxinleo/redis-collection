<?php
namespace SwoftTest\Testing;

use Xin\RedisCollection\ZSetCollection;

class DemoCollection extends ZSetCollection
{
    protected $prefix = 'demo:';

    protected $redis;

    public function __construct()
    {
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1');
        $this->redis->auth('910123');
    }

    public function reload($parentId)
    {
        if ($parentId == 1) {
            return [1 => 0, 2 => 1];
        }

        return [];
    }

    public function redis()
    {
        return $this->redis;
    }
}