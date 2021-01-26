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
namespace SwoftTest\Testing;

use Xin\RedisCollection\HyperLogLogCollection;

class DemoHyLogCollection extends HyperLogLogCollection
{
    protected $prefix = 'demohylog:';

    protected $redis;

    public function __construct()
    {
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1');
    }

    public function redis()
    {
        return $this->redis;
    }

    public function reload($parentId): array
    {
        return [uniqid('kytest')];
    }

    public function prefix(): string
    {
        return $this->prefix;
    }

    public function setExist($value)
    {
        $this->exist = $value;
    }
}
