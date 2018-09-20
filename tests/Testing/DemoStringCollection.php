<?php


namespace SwoftTest\Testing;


use Xin\RedisCollection\StringCollection;

class DemoStringCollection extends StringCollection
{
    /**
     * redis key
     * @var string
     */
    protected $prefix = 'demostring:';

    protected $redis;

    public function __construct()
    {
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1');
        $this->redis->auth('910123');
    }

    public function redis()
    {
        return $this->redis;
    }
}