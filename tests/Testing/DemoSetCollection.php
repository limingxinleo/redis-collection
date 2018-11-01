<?php


namespace SwoftTest\Testing;


use Xin\RedisCollection\SetCollection;

class DemoSetCollection extends SetCollection
{
    protected $prefix = 'demoset:';

    protected $redis;

    public function __construct()
    {
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1');
        $this->redis->auth('910123');
    }

    public function reload($parentId): array
    {
        return [1, 2, 'a'];
    }

    public function redis()
    {
        return $this->redis;
    }
}