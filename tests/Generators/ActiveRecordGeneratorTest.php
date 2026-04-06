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
            template: 'default',
            namespace: 'App\\Model',
            tableName: 'user',
            baseClass: ActiveRecord::class,
        );

        $files = $generator->generate($command);

        $this->assertNotEmpty($files);
    }

    public function testGenerateWithGettersSetters(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            template: 'default',
            namespace: 'App\\Model',
            tableName: 'user',
            baseClass: ActiveRecord::class, generateGettersSetters: true,
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        $this->assertStringContainsString('public function getId(): ?int', $content);
        $this->assertStringContainsString('public function setId(int $id): void', $content);
        $this->assertStringContainsString('public function getEmail(): ?string', $content);
        $this->assertStringContainsString('public function setEmail(string $email): void', $content);
        $this->assertStringContainsString('public function getName(): ?string', $content);
        $this->assertStringContainsString('public function setName(?string $name): void', $content);
        $this->assertStringContainsString('public function getAddress(): ?string', $content);
        $this->assertStringContainsString('public function setAddress(?string $address): void', $content);
        $this->assertStringContainsString('public function getAge(): ?int', $content);
        $this->assertStringContainsString('public function setAge(?int $age): void', $content);
        $this->assertStringContainsString('public function getProfile_id(): ?int', $content);
        $this->assertStringContainsString('public function setProfile_id(?int $profile_id): void', $content);
        $this->assertStringContainsString('public function getScore(): ?float', $content);
        $this->assertStringContainsString('public function setScore(?float $score): void', $content);
        $this->assertStringContainsString('public function getIs_verified(): bool', $content);
        $this->assertStringContainsString('public function setIs_verified(bool $is_verified): void', $content);
        $this->assertStringContainsString('public function getCreated_at(): ?\\DateTimeImmutable', $content);
        $this->assertStringContainsString('public function setCreated_at(\\DateTimeImmutable $created_at): void', $content);
    }

    public function testGenerateWithDefaultValues(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            template: 'default',
            namespace: 'App\\Model',
            tableName: 'user',
            baseClass: ActiveRecord::class,
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        // Should have default value for is_verified column
        $this->assertStringContainsString("\$is_verified = false;", $content);
    }

    public function testGenerateWithPrivateProperties(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            template: 'default',
            namespace: 'App\\Model',
            tableName: 'user',
            baseClass: ActiveRecord::class,
            propertyVisibility: 'private',
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        $this->assertStringContainsString('private int $id;', $content);
        $this->assertStringContainsString('private string $email;', $content);
        $this->assertStringContainsString('private ?string $name;', $content);
        $this->assertStringContainsString('private ?string $address;', $content);
        $this->assertStringContainsString('private ?int $age;', $content);
        $this->assertStringContainsString('private ?int $profile_id;', $content);
        $this->assertStringContainsString('private ?float $score;', $content);
        $this->assertStringContainsString('private bool $is_verified', $content);
        $this->assertStringContainsString('private \\DateTimeImmutable $created_at;', $content);
    }

    public function testGenerateWithPublicProperties(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            template: 'default',
            namespace: 'App\\Model',
            tableName: 'user',
            baseClass: ActiveRecord::class,
            propertyVisibility: 'public',
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        $this->assertStringContainsString('public int $id;', $content);
        $this->assertStringContainsString('public string $email;', $content);
        $this->assertStringContainsString('public ?string $name;', $content);
        $this->assertStringContainsString('public ?string $address;', $content);
        $this->assertStringContainsString('public ?int $age;', $content);
        $this->assertStringContainsString('public ?int $profile_id;', $content);
        $this->assertStringContainsString('public ?float $score;', $content);
        $this->assertStringContainsString('public bool $is_verified', $content);
        $this->assertStringContainsString('public \\DateTimeImmutable $created_at;', $content);
    }

    public function testGenerateWithRepositoryTrait(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            template: 'default',
            namespace: 'App\\Model',
            tableName: 'user',
            baseClass: ActiveRecord::class,
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
            template: 'default',
            namespace: 'App\\Model',
            tableName: 'user',
            baseClass: ActiveRecord::class,
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
            template: 'default',
            namespace: 'App\\Model',
            tableName: 'user_profile',
            baseClass: ActiveRecord::class, generateRelations: true,
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
            template: 'default',
            namespace: 'App\\Model',
            tableName: 'user',
            baseClass: ActiveRecord::class,
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        // Email column is not nullable
        $this->assertStringContainsString('string $email;', $content);
        // Name is nullable
        $this->assertStringContainsString('?string $name;', $content);
    }

    public function testGenerateWithDifferentTypes(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            template: 'default',
            namespace: 'App\\Model',
            tableName: 'user',
            baseClass: ActiveRecord::class,
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        // Test various PHP types
        $this->assertStringContainsString('int $id;', $content);
        $this->assertStringContainsString('string $email;', $content);
        $this->assertStringContainsString('?string $name;', $content);
        $this->assertStringContainsString('?string $address;', $content);
        $this->assertStringContainsString('?int $age;', $content);
        $this->assertStringContainsString('?int $profile_id;', $content);
        $this->assertStringContainsString('?float $score;', $content);
        $this->assertStringContainsString('bool $is_verified', $content);
        $this->assertStringContainsString('\\DateTimeImmutable $created_at;', $content);
    }

    public function testInvalidTableName(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            template: 'default',
            namespace: 'App\\Model',
            tableName: 'user2',
            baseClass: ActiveRecord::class,
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
            template: 'test',
            namespace: '0App',
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
            template: 'custom',
            namespace: 'App\\Model',
            tableName: 'user',
            baseClass: ActiveRecord::class,
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
