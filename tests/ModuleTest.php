<?php

namespace Yiisoft\Yii\Gii\Tests;

use Yiisoft\Yii\Gii\Module;
use yii\tests\TestCase;

class ModuleTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->mockWebApplication();
    }

    public function testDefaultVersion(): void
    {
        $module = new Module('gii', $this->app);

        $this->assertEquals('1.0', $module->getVersion());
    }
}
