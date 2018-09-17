<?php
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://doc.swoft.org
 * @contact  limingxin@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */
namespace SwoftTest\Cases;

use SwoftTest\Testing\DemoCollection;

class ZSetCollectionTest extends AbstractTestCase
{
    protected $pid = 1;

    public function testAdd()
    {
        $collection = new DemoCollection();
        $collection->add($this->pid, 2, 3);
    }
}
