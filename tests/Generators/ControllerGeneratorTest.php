<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Tests\Generators;

use Yiisoft\Injector\Injector;
use Yiisoft\Yii\Gii\Exception\InvalidGeneratorCommandException;
use Yiisoft\Yii\Gii\Generator\Controller\Command;
use Yiisoft\Yii\Gii\Generator\Controller\Generator;
use Yiisoft\Yii\Gii\ParametersProvider;
use Yiisoft\Yii\Gii\Tests\TestCase;

final class ControllerGeneratorTest extends TestCase
{
    public function testValidGenerator(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            controllerClass: 'TestController',
            actions: ['index', 'edit', 'view'],
            template: 'default',
        );

        $files = $generator->generate($command);

        $this->assertNotEmpty($files);
    }

    public function testInvalidGenerator(): never
    {
        $generator = $this->createGenerator(
            templates: [
                'default' => dirname(__DIR__ . '../templates'),
            ],
        );
        $command = new Command(
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
                Generator::getId() => [
                    'custom' => '@src/templates/controller',
                ],
            ],
        );
        $command = new Command(
            controllerClass: 'TestController',
            actions: ['index', 'edit', 'view'],
            template: 'custom',
        );

        $files = $generator->generate($command);
        $this->assertNotEmpty($files);
        foreach ($files as $file) {
            $this->assertEmpty($file->getContent());
        }
    }

    private function createGenerator(...$params): Generator
    {
        $injector = new Injector(
            $this->getContainer([
                ParametersProvider::class => new ParametersProvider(...$params),
            ])
        );

        return $injector->make(Generator::class);
    }
}
