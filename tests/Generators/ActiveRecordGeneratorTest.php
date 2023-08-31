<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Tests\Generators;

use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\Injector\Injector;
use Yiisoft\Yii\Gii\Exception\InvalidGeneratorCommandException;
use Yiisoft\Yii\Gii\Generator\ActiveRecord\Command;
use Yiisoft\Yii\Gii\Generator\ActiveRecord\Generator;
use Yiisoft\Yii\Gii\ParametersProvider;
use Yiisoft\Yii\Gii\Tests\TestCase;

final class ActiveRecordGeneratorTest extends TestCase
{
    public function testValidGenerator(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            namespace: 'App\\Model',
            tableName: 'user',
            baseClass: ActiveRecord::class,
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
        $command = new Command(
            namespace: '0App',
            template: 'test',
        );

        $this->expectException(InvalidGeneratorCommandException::class);
        $generator->generate($command);
    }

    public function testCustomTemplate(): void
    {
        $generator = $this->createGenerator(
            templates: [
                Generator::getId() => [
                    'custom' => '@src/templates/active-record',
                ],
            ],
        );
        $command = new Command(
            namespace: 'App\\Model',
            tableName: 'user',
            baseClass: ActiveRecord::class,
            template: 'custom',
        );

        $files = $generator->generate($command);
        $this->assertNotEmpty($files);
        $this->assertMatchesRegularExpression("/final custom class/", reset($files)->getContent());
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
