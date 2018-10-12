<?php


namespace SwoftTest\Testing;


use Xin\RedisCollection\HashCollection;

class DemoHashCollection2 extends HashCollection
{
    protected $prefix = 'demohash2:';

    protected $ttl = 3600;

    protected $redis;

    public function __construct()
    {
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1');
        $this->redis->auth('910123');
    }

    public function reload($parentId)
    {
        return [
            'id' => 1,
            'name' => 'limx'
        ];
    }

    public function redis()
    {
        return $this->redis;
    }
}