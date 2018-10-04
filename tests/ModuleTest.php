<?php

namespace yiiunit\gii;

use yii\gii\Module;
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
        $this->app->extensions['yiisoft/yii2-gii'] = [
            'name' => 'yiisoft/yii2-gii',
            'version' => '2.0.6',
        ];

        $module = new Module('gii', $this->app);

        $this->assertEquals('1.0', $module->getVersion());
    }
}