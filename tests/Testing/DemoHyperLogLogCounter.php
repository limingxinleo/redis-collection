<?php
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://doc.swoft.org
 * @contact  limingxin@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace SwoftTest\Testing;

use Xin\RedisCollection\HyperLogLogCounter;

class DemoHyperLogLogCounter extends HyperLogLogCounter
{
    protected $prefix = 'demohyper:';

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
            'a1', 'a2'
        ];
    }

    public function redis()
    {
        return $this->redis;
    }
}
