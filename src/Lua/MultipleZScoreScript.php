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
namespace Xin\RedisCollection;

class MultipleZScoreScript implements ScriptInterface
{
    public function getScript(): string
    {
        return <<<'LUA'
    local values = {};
    for i,v in ipairs(ARGV) do 
        values[i] = redis.call('zscore',KEYS[1],v);
    end
    return values;
LUA;
    }

    public function getName(): string
    {
        return 'MultipleZScoreScript';
    }
}
