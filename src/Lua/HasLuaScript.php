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

trait HasLuaScript
{
    /**
     * @var array<string, string>
     */
    protected $shaList = [];

    /**
     * @var bool
     */
    protected $loadScript = true;

    public function runScript(ScriptInterface $script, array $keys, ?int $num = null)
    {
        if ($num === null) {
            $num = count($keys);
        }

        /** @var \Redis $redis */
        $redis = $this->redis();

        if ($this->loadScript) {
            if ($sha = $this->shaList[$script->getName()] ?? null) {
                return $redis->evalSha($sha, $keys, $num);
            }

            $this->shaList[$script->getName()] = $sha = $redis->script('load', $script->getScript());
            return $redis->evalSha($sha, $keys, $num);
        }

        return $redis->eval($script->getScript(), $keys, $num);
    }
}
