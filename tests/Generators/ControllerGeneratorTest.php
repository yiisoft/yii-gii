<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Tests\Generators;

use Yiisoft\Yii\Gii\CodeFile;
use Yiisoft\Yii\Gii\Generator\Controller\Generator as ControllerGenerator;
use Yiisoft\Yii\Gii\Tests\TestCase;

/**
 * ControllerGeneratorTest checks that Gii controller generator produces valid results
 */
class ControllerGeneratorTest extends TestCase
{
    public function testValidGenerator(): void
    {
        $generator = $this->createGenerator();
        $generator->load(
            [
                'template' => 'default',
                'controllerClass' => 'TestController',
                'actions' => 'index,edit,view',
            ]
        );

        $generator->validate();

        $this->assertNotEmpty($generator->generate());
        $this->assertContainsOnlyInstancesOf(CodeFile::class, $generator->generate());
        $this->assertCount(4, $generator->generate());
        $this->assertFalse($generator->hasErrors());
        $this->assertEquals(['edit', 'index', 'view'], $generator->getActionIDs());
    }

    public function testInvalidGenerator(): void
    {
        $generator = $this->createGenerator();
        $generator->load(
            [
                'template' => 'test',
                'controllerClass' => 'Wr0ngContr0ller',
                'actions' => 'index,ed1t,view',
                'templates' => [
                    'default' => dirname(__DIR__ . '../templates'),
                ],
            ]
        );

        $generator->validate();

        $this->assertTrue($generator->hasErrors());
        $this->assertNotEmpty($generator->getErrors()['template']);
        $this->assertNotEmpty($generator->getErrors()['controllerClass']);
    }

    public function testCustomTemplate(): void
    {
        $generator = $this->createGenerator();
        $generator->load(
            [
                'template' => 'custom',
                'controllerClass' => 'TestController',
                'actions' => 'index,edit,view',
                'templates' => [
                    'custom' => '@app/templates/custom',
                ],
            ]
        );

        $validationResult = $generator->validate();

        $this->assertFalse(
            $generator->hasErrors(),
            implode("\n", $validationResult->getAttributeErrorMessages('template'))
        );
        $this->assertContainsOnlyInstancesOf(CodeFile::class, $generator->generate());
    }

    private function createGenerator(): ControllerGenerator
    {
        return $this->getContainer()->get(ControllerGenerator::class);
    }
}
