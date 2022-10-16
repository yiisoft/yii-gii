<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Tests\Generators;

use Throwable;
use Yiisoft\Injector\Injector;
use Yiisoft\Yii\Gii\CodeFile;
use Yiisoft\Yii\Gii\Exception\InvalidGeneratorCommandException;
use Yiisoft\Yii\Gii\Generator\Controller\ControllerCommand;
use Yiisoft\Yii\Gii\Generator\Controller\ControllerGenerator as ControllerGenerator;
use Yiisoft\Yii\Gii\GiiParametersProvider;
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
            actions: ['index','edit','view'],
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
                'custom' => '@app/templates/custom',
            ],
        );
        $command = new ControllerCommand(
            controllerClass: 'TestController',
            actions: ['index', 'edit', 'view'],
            template: 'custom',
        );

        try {
            $generator->generate($command);
        } catch (InvalidGeneratorCommandException $e) {
            $this->assertFalse(
                $e->getResult()->isValid(),
                print_r($e->getResult()->getErrors(), true)
            );
            $this->assertContainsOnlyInstancesOf(CodeFile::class, []);
            throw $e;
        }

        // TODO: fix test
        $this->markTestIncomplete('The result should be invalid.');
    }

    private function createGenerator(...$params): ControllerGenerator
    {
        $injector = new Injector($this->getContainer());

        return $injector->make(ControllerGenerator::class, [
            new GiiParametersProvider(...$params),
        ]);
    }
}
