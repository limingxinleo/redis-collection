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
namespace Xin\RedisCollection\Lua;

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

    public function runScript(ScriptInterface $script)
    {
        $redis = $this->redis();

        if ($this->loadScript) {
            if ($sha = $this->shaList[$script->getName()] ?? null) {
                return $script->formatOutput(
                    $redis->evalSha($sha, $script->getArgs(), $script->getKeyNumber())
                );
            }

            $this->shaList[$script->getName()] = $sha = $redis->script('load', $script->getScript());
            return $script->formatOutput(
                $redis->evalSha($sha, $script->getArgs(), $script->getKeyNumber())
            );
        }

        return $script->formatOutput(
            $redis->eval($script->getScript(), $script->getArgs(), $script->getKeyNumber())
        );
    }
}
