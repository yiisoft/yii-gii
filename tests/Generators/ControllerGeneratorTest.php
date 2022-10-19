<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Tests\Generators;

use Yiisoft\Injector\Injector;
use Yiisoft\Yii\Gii\Exception\InvalidGeneratorCommandException;
use Yiisoft\Yii\Gii\Generator\Controller\ControllerCommand;
use Yiisoft\Yii\Gii\Generator\Controller\ControllerGenerator as ControllerGenerator;
use Yiisoft\Yii\Gii\ParametersProvider;
use Yiisoft\Yii\Gii\Tests\TestCase;

/**
 * ControllerGeneratorTest checks that Gii controller generator produces valid results
 */
class ControllerGeneratorTest extends TestCase
{
    public function testValidGenerator(): void
    {
        $generator = $this->createGenerator();
        $command = new ControllerCommand(
            controllerClass: 'TestController',
            actions: ['index', 'edit', 'view'],
            template: 'default',
        );

        $files = $generator->generate($command);

        $this->assertNotEmpty($files);
    }

    public function testInvalidGenerator(): void
    {
        $generator = $this->createGenerator(
            templates: [
                'default' => dirname(__DIR__ . '../templates'),
            ],
        );
        $command = new ControllerCommand(
            controllerClass: 'Wr0ngContr0ller',
            actions: ['index', 'ed1t', 'view'],
            template: 'test',
        );

        $this->expectException(InvalidGeneratorCommandException::class);
        $files = $generator->generate($command);

//        $this->assertFalse($result->isValid(), print_r($result->getErrors(), true));

        // TODO: fix test
        $this->markTestIncomplete('The template should be incomplete.'); // but why?
//        $this->assertNotEmpty($result->getAttributeErrorMessages('template'));
        $this->assertNotEmpty($result->getAttributeErrorMessages('controllerClass'));
    }

    public function testCustomTemplate(): void
    {
        $generator = $this->createGenerator(
            templates: [
                'custom' => '@src/templates/custom',
            ],
        );
        $command = new ControllerCommand(
            controllerClass: 'TestController',
            actions: ['index', 'edit', 'view'],
            template: 'custom',
        );

        $this->expectException(InvalidGeneratorCommandException::class);
        $generator->generate($command);
    }

    private function createGenerator(...$params): ControllerGenerator
    {
        $injector = new Injector($this->getContainer());

        return $injector->make(ControllerGenerator::class, [
            new ParametersProvider(...$params),
        ]);
    }
}
