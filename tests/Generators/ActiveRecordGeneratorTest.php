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

    public function testGenerateWithGettersSetters(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            namespace: 'App\\Model',
            tableName: 'user',
            baseClass: ActiveRecord::class,
            template: 'default',
            generateGettersSetters: true,
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        $this->assertStringContainsString('public function getId(): ?int', $content);
        $this->assertStringContainsString('public function setId(?int $id): void', $content);
        $this->assertStringContainsString('public function getUsername(): string', $content);
        $this->assertStringContainsString('public function setUsername(string $username): void', $content);
        $this->assertStringContainsString('public function getStatus(): string', $content);
    }

    public function testGenerateWithDefaultValues(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            namespace: 'App\\Model',
            tableName: 'user',
            baseClass: ActiveRecord::class,
            template: 'default',
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        // Should have default value for status column
        $this->assertStringContainsString("\$status = 'active'", $content);
        // Should have default value for is_active column
        $this->assertStringContainsString('$is_active = 1', $content);
    }

    public function testGenerateWithPrivateProperties(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            namespace: 'App\\Model',
            tableName: 'user',
            baseClass: ActiveRecord::class,
            template: 'default',
            propertyVisibility: 'private',
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        $this->assertStringContainsString('private int $id', $content);
        $this->assertStringContainsString('private string $username', $content);
    }

    public function testGenerateWithPublicProperties(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            namespace: 'App\\Model',
            tableName: 'user',
            baseClass: ActiveRecord::class,
            template: 'default',
            propertyVisibility: 'public',
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        $this->assertStringContainsString('public int $id', $content);
        $this->assertStringContainsString('public string $username', $content);
    }

    public function testGenerateWithRepositoryTrait(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            namespace: 'App\\Model',
            tableName: 'user',
            baseClass: ActiveRecord::class,
            template: 'default',
            useRepositoryTrait: true,
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        $this->assertStringContainsString('use Yiisoft\ActiveRecord\Trait\RepositoryTrait;', $content);
        $this->assertStringContainsString('use RepositoryTrait;', $content);
    }

    public function testGenerateWithPrivatePropertiesTrait(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            namespace: 'App\\Model',
            tableName: 'user',
            baseClass: ActiveRecord::class,
            template: 'default',
            usePrivatePropertiesTrait: true,
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        $this->assertStringContainsString('use Yiisoft\ActiveRecord\Trait\PrivatePropertiesTrait;', $content);
        $this->assertStringContainsString('use PrivatePropertiesTrait;', $content);
    }

    public function testGenerateWithRelations(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            namespace: 'App\\Model',
            tableName: 'user_profile',
            baseClass: ActiveRecord::class,
            template: 'default',
            generateRelations: true,
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        // Should have relation methods
        $this->assertStringContainsString('public function relationQuery(string $name): ActiveQueryInterface', $content);
        $this->assertStringContainsString('public function getUser()', $content);
        $this->assertStringContainsString('public function getUserQuery(): ActiveQueryInterface', $content);
        $this->assertStringContainsString('use Yiisoft\ActiveRecord\ActiveQueryInterface;', $content);
    }

    public function testGenerateWithNullableColumns(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            namespace: 'App\\Model',
            tableName: 'user',
            baseClass: ActiveRecord::class,
            template: 'default',
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        // Email column is nullable
        $this->assertStringContainsString('?string $email', $content);
        // Username is not nullable
        $this->assertStringContainsString('string $username', $content);
    }

    public function testGenerateWithDifferentTypes(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            namespace: 'App\\Model',
            tableName: 'user',
            baseClass: ActiveRecord::class,
            template: 'default',
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        // Test various PHP types
        $this->assertStringContainsString('int $id', $content);
        $this->assertStringContainsString('string $username', $content);
        $this->assertStringContainsString('?int $age', $content);
        $this->assertStringContainsString('?float $balance', $content);
    }

    public function testInvalidTableName(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            namespace: 'App\\Model',
            tableName: 'user2',
            baseClass: ActiveRecord::class,
            template: 'default',
        );

        $this->expectException(InvalidGeneratorCommandException::class);
        $generator->generate($command);
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
        $this->assertMatchesRegularExpression('/final custom class/', reset($files)->getContent());
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
