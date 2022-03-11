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
    /**
     * @var array
     */
    private $members;

    /**
     * @var string
     */
    private $key;

    public function __construct(string $key, array $members = [])
    {
        $this->key = $key;
        $this->members = $members;
    }

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

    public function getArgs(): array
    {
        $args = $this->members;
        array_unshift($args, $this->key);
        return $args;
    }

    public function getKeyNumber(): int
    {
        return 1;
    }

    public function formatOutput(mixed $output): mixed
    {
        $return = [];
        foreach ($this->members as $i => $input) {
            if ($value = $output[$i] ?? false and $value !== false) {
                $return[$input] = $value;
            }
        }
        return $return;
    }
}
