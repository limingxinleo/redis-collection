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

interface ScriptInterface
{
    public function getScript(): string;

    public function getName(): string;

    public function getArgs(): array;

    public function getKeyNumber(): int;

    public function formatOutput($output);
}
