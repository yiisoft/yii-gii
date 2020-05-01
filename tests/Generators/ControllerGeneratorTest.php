<?php

namespace Yiisoft\Yii\Gii\Tests\Generators;

use Yiisoft\Aliases\Aliases;
use Yiisoft\Validator\ResultSet;
use Yiisoft\View\View;
use Yiisoft\Yii\Gii\CodeFile;
use Yiisoft\Yii\Gii\Generator\Controller\Generator as ControllerGenerator;
use Yiisoft\Yii\Gii\Parameters;
use Yiisoft\Yii\Gii\Tests\GiiTestCase;

/**
 * ControllerGeneratorTest checks that Gii controller generator produces valid results
 */
class ControllerGeneratorTest extends GiiTestCase
{
    public function testValidGenerator()
    {
        $generator = new ControllerGenerator(
            $this->getContainer()->get(Aliases::class),
            $this->getContainer()->get(Parameters::class),
            $this->getContainer()->get(View::class)
        );
        $generator->load(
            [
                'template' => 'default',
                'controllerClass' => 'TestController',
                'actions' => 'index,edit,view'
            ]
        );

        $validationResult = $generator->validate();

        $this->assertInstanceOf(ResultSet::class, $validationResult);
        $this->assertNotEmpty($generator->generate());
        $this->assertContainsOnlyInstancesOf(CodeFile::class, $generator->generate());
        $this->assertCount(4, $generator->generate());
        $this->assertFalse($generator->hasErrors());
        $this->assertEquals(['edit', 'index', 'view'], $generator->getActionIDs());
    }

    public function testInvalidGenerator()
    {
        $generator = new ControllerGenerator(
            $this->getContainer()->get(Aliases::class),
            $this->getContainer()->get(Parameters::class),
            $this->getContainer()->get(View::class)
        );
        $generator->load(
            [
                'template' => 'test',
                'controllerClass' => 'Wr0ngContr0ller',
                'actions' => 'index,ed1t,view',
                'templates' => [
                    'default' => dirname(__DIR__ . '../templates')
                ]
            ]
        );

        $validationResult = $generator->validate();

        $this->assertInstanceOf(ResultSet::class, $validationResult);
        $this->assertTrue($generator->hasErrors());
        $this->assertNotEmpty($generator->getErrors()['template']);
        $this->assertNotEmpty($generator->getErrors()['controllerClass']);
    }
}
