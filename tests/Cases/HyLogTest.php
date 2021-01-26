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
namespace SwoftTest\Cases;

use SwoftTest\Testing\DemoHyLogCollection;

/**
 * @internal
 * @coversNothing
 */
class HyLogTest extends AbstractTestCase
{
    public function testHyLog()
    {
        $id = 'test';
        $model = new DemoHyLogCollection();
        $model->delete($id);
        $model->add($id, [uniqid(), uniqid('ky')]);
        $this->assertEquals($model->count($id), 4);
    }

    public function testHyLogExist()
    {
        $id = 'test';
        $model = new DemoHyLogCollection();
        $model->setExist(true);
        $model->delete($id);

        $model->add($id, [uniqid(), uniqid('ky')]);
        $this->assertEquals($model->count($id), 2);
    }
}
