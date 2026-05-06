<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Tests\Generators\ActiveRecord;

use stdClass;
use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\Injector\Injector;
use Yiisoft\Yii\Gii\Exception\InvalidGeneratorCommandException;
use Yiisoft\Yii\Gii\Generator\ActiveRecord\Command;
use Yiisoft\Yii\Gii\Generator\ActiveRecord\Generator;
use Yiisoft\Yii\Gii\ParametersProvider;
use Yiisoft\Yii\Gii\Tests\TestCase;

use function dirname;

final class ActiveRecordGeneratorTest extends TestCase
{
    public function testValidGenerator(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            table: 'user',
            namespace: 'Yiisoft\\Yii\\Gii\\Tests\\Model',
        );

        $files = $generator->generate($command);

        $this->assertNotEmpty($files);
    }

    public function testGenerateWithGettersSetters(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            table: 'user',
            namespace: 'Yiisoft\\Yii\\Gii\\Tests\\Model',
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
        $this->assertStringContainsString('public function getProfileId(): ?int', $content);
        $this->assertStringContainsString('public function setProfileId(?int $profile_id): void', $content);
        $this->assertStringContainsString('public function getScore(): ?float', $content);
        $this->assertStringContainsString('public function setScore(?float $score): void', $content);
        $this->assertStringContainsString('public function getIsVerified(): bool', $content);
        $this->assertStringContainsString('public function setIsVerified(bool $is_verified): void', $content);
        $this->assertStringContainsString('public function getCreatedAt(): null|\\Yiisoft\\Db\\Expression\\Expression|\\DateTimeImmutable', $content);
        $this->assertStringContainsString('public function setCreatedAt(\\Yiisoft\\Db\\Expression\\Expression|\\DateTimeImmutable $created_at): void', $content);
        $this->assertStringContainsString('public function getLuckyNumbers(): mixed', $content);
        $this->assertStringContainsString('public function setLuckyNumbers(mixed $lucky_numbers): void', $content);
    }

    public function testGenerateWithDefaultValues(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            table: 'user',
            namespace: 'Yiisoft\\Yii\\Gii\\Tests\\Model',
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        // Should have default value for is_verified column
        $this->assertStringContainsString('$name = null;', $content);
        $this->assertStringContainsString('$address = null;', $content);
        $this->assertStringContainsString('$age = null;', $content);
        $this->assertStringContainsString('$profile_id = null;', $content);
        $this->assertStringContainsString('$score = null;', $content);
        $this->assertStringContainsString('$is_verified = false;', $content);
        $this->assertStringContainsString('$lucky_numbers = [1,2,3];', $content);
    }

    public function testGenerateWithPrivateProperties(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            table: 'user',
            namespace: 'Yiisoft\\Yii\\Gii\\Tests\\Model',
            propertyVisibility: 'private',
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        $this->assertStringContainsString('private int $id', $content);
        $this->assertStringContainsString('private string $email', $content);
        $this->assertStringContainsString('private ?string $name', $content);
        $this->assertStringContainsString('private ?string $address', $content);
        $this->assertStringContainsString('private ?int $age', $content);
        $this->assertStringContainsString('private ?int $profile_id', $content);
        $this->assertStringContainsString('private ?float $score', $content);
        $this->assertStringContainsString('private bool $is_verified', $content);
        $this->assertStringContainsString('private \\Yiisoft\\Db\\Expression\\Expression|\\DateTimeImmutable $created_at', $content);
        $this->assertStringContainsString('private mixed $lucky_numbers', $content);
    }

    public function testGenerateWithPublicProperties(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            table: 'user',
            namespace: 'Yiisoft\\Yii\\Gii\\Tests\\Model',
            propertyVisibility: 'public',
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        $this->assertStringContainsString('public int $id', $content);
        $this->assertStringContainsString('public string $email', $content);
        $this->assertStringContainsString('public ?string $name', $content);
        $this->assertStringContainsString('public ?string $address', $content);
        $this->assertStringContainsString('public ?int $age', $content);
        $this->assertStringContainsString('public ?int $profile_id', $content);
        $this->assertStringContainsString('public ?float $score', $content);
        $this->assertStringContainsString('public bool $is_verified', $content);
        $this->assertStringContainsString('public \\Yiisoft\\Db\\Expression\\Expression|\\DateTimeImmutable $created_at', $content);
        $this->assertStringContainsString('public mixed $lucky_numbers', $content);
    }

    public function testGenerateWithRepositoryTrait(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            table: 'user',
            namespace: 'Yiisoft\\Yii\\Gii\\Tests\\Model',
            useRepositoryTrait: true,
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        $this->assertStringContainsString('use Yiisoft\\ActiveRecord\\Trait\\RepositoryTrait;', $content);
        $this->assertStringContainsString('use RepositoryTrait;', $content);
    }

    public function testGenerateWithPrivatePropertiesTrait(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            table: 'user',
            namespace: 'Yiisoft\\Yii\\Gii\\Tests\\Model',
            propertyVisibility: 'private',
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        $this->assertStringContainsString('use Yiisoft\\ActiveRecord\\Trait\\PrivatePropertiesTrait;', $content);
        $this->assertStringContainsString('use PrivatePropertiesTrait;', $content);
    }

    public function testGenerateWithRelations(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            table: 'user_profile',
            namespace: 'Yiisoft\\Yii\\Gii\\Tests\\Model',
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        // Should have relation methods
        $this->assertStringContainsString('public function relationQuery(string $name): ActiveQueryInterface', $content);
        $this->assertStringContainsString('public function getUser()', $content);
        $this->assertStringContainsString('public function getUserQuery(): ActiveQueryInterface', $content);
        $this->assertStringContainsString('use Yiisoft\\ActiveRecord\\ActiveQueryInterface;', $content);
    }

    public function testGenerateWithNullableColumns(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            table: 'user',
            namespace: 'Yiisoft\\Yii\\Gii\\Tests\\Model',
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        // Email column is not nullable
        $this->assertStringContainsString('string $email;', $content);
        // Name is nullable
        $this->assertStringContainsString('?string $name = null;', $content);
        $this->assertStringContainsString('?string $address = null;', $content);
        $this->assertStringContainsString('?int $age = null;', $content);
        $this->assertStringContainsString('?int $profile_id = null;', $content);
        $this->assertStringContainsString('?float $score = null;', $content);
    }

    public function testGenerateWithDifferentTypes(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            table: 'user',
            namespace: 'Yiisoft\\Yii\\Gii\\Tests\\Model',
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        // Test various PHP types
        $this->assertStringContainsString('int $id', $content);
        $this->assertStringContainsString('string $email', $content);
        $this->assertStringContainsString('?string $name', $content);
        $this->assertStringContainsString('?string $address', $content);
        $this->assertStringContainsString('?int $age', $content);
        $this->assertStringContainsString('?int $profile_id', $content);
        $this->assertStringContainsString('?float $score', $content);
        $this->assertStringContainsString('bool $is_verified', $content);
        $this->assertStringContainsString('\\Yiisoft\\Db\\Expression\\Expression|\\DateTimeImmutable $created_at', $content);
        $this->assertStringContainsString('mixed $lucky_numbers', $content);
    }

    public function testInvalidTableName(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            table: 'user2',
        );

        $this->expectException(InvalidGeneratorCommandException::class);
        $generator->generate($command);
    }

    public function testInvalidParentClass(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            table: 'user',
            namespace: 'Yiisoft\\Yii\\Gii\\Tests\\Model',
            parentClass: stdClass::class,
        );

        $this->expectException(InvalidGeneratorCommandException::class);
        $generator->generate($command);
    }

    public function testNonExistentParentClass(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            table: 'user',
            namespace: 'Yiisoft\\Yii\\Gii\\Tests\\Model',
            parentClass: 'NonExistent\\ParentClass',
        );

        $this->expectException(InvalidGeneratorCommandException::class);
        $generator->generate($command);
    }

    public function testGenerateWithCustomParentClass(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            table: 'user',
            namespace: 'Yiisoft\\Yii\\Gii\\Tests\\Model',
            parentClass: ActiveRecord::class,
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        $this->assertStringContainsString('use Yiisoft\\ActiveRecord\\ActiveRecord;', $content);
        $this->assertStringContainsString('extends ActiveRecord', $content);
    }

    public function testInvalidGenerator(): void
    {
        $generator = $this->createGenerator(
            templates: [
                'default' => dirname(__DIR__ . '../templates'),
            ],
        );
        $command = new Command(
            table: 'user',
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
            table: 'user',
            namespace: 'Yiisoft\\Yii\\Gii\\Tests\\Model',
            template: 'custom',
        );

        $files = $generator->generate($command);
        $this->assertNotEmpty($files);
        $this->assertStringContainsString("\nfinal custom class User", reset($files)->getContent());
    }

    public function testGenerateConstructor(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            table: 'user',
            namespace: 'Yiisoft\\Yii\\Gii\\Tests\\Model',
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        $this->assertStringContainsStringIgnoringLineEndings(
            <<<'PHP'
                public function __construct()
                {
                    $this->created_at = new Expression('CURRENT_TIMESTAMP', []);
                }
            PHP,
            $content,
        );
    }

    public function testGenerateRelations(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            table: 'user',
            namespace: 'Yiisoft\\Yii\\Gii\\Tests\\Model',
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        $this->assertStringContainsStringIgnoringLineEndings(
            <<<'PHP'
                public function relationQuery(string $name): ActiveQueryInterface
                {
                    return match ($name) {
                        'profile' => $this->getProfileQuery(),
                        default => parent::relationQuery($name),
                    };
                }
            PHP,
            $content,
        );
    }

    public function testGenerateRelationGetter(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            table: 'user',
            namespace: 'Yiisoft\\Yii\\Gii\\Tests\\Model',
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        $this->assertStringContainsStringIgnoringLineEndings(
            <<<'PHP'
                public function getProfile(): ?UserProfile
                {
                    return $this->relation('profile');
                }
            PHP,
            $content,
        );
    }

    public function testGenerateRelationQueryGetter(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            table: 'user',
            namespace: 'Yiisoft\\Yii\\Gii\\Tests\\Model',
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        $this->assertStringContainsStringIgnoringLineEndings(
            <<<'PHP'
                public function getProfileQuery(): ActiveQueryInterface
                {
                    return $this->hasOne(UserProfile::class, ['id' => 'profile_id'])->inverseOf('user');
                }
            PHP,
            $content,
        );
    }

    public function testGenerateInverseHasOneRelation(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            table: 'user_profile',
            namespace: 'Yiisoft\\Yii\\Gii\\Tests\\Model',
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        $this->assertStringContainsString('public function getUser()', $content);
        $this->assertStringContainsString('public function getUserQuery(): ActiveQueryInterface', $content);
        $this->assertStringContainsString("return \$this->hasOne(User::class, ['profile_id' => 'id'])->inverseOf('profile');", $content);
    }

    public function testGenerateWithCollisionResolution(): void
    {
        $generator = $this->createGenerator();
        $command = new Command(
            table: 'document',
            namespace: 'Yiisoft\\Yii\\Gii\\Tests\\Model',
        );

        $files = $generator->generate($command);
        $content = reset($files)->getContent();

        // Both `user_id` and `user_uuid` normalize to `user` which causes a name collision.
        // The generator should resolve this by falling back to the full column name (no suffix stripping).
        // `user_id` => `userId`, `user_uuid` => `userUuid`
        $this->assertStringContainsString('public function getUserId()', $content);
        $this->assertStringContainsString('public function getUserUuid()', $content);
        $this->assertStringContainsString("'userId' => \$this->getUserIdQuery()", $content);
        $this->assertStringContainsString("'userUuid' => \$this->getUserUuidQuery()", $content);

        // The original (colliding) name `user` should NOT appear as a relation
        $this->assertStringNotContainsString("'user' => \$this->getUserQuery()", $content);
    }

    private function createGenerator(...$params): Generator
    {
        $injector = new Injector(
            $this->getContainer([
                ParametersProvider::class => new ParametersProvider(...$params),
            ]),
        );

        return $injector->make(Generator::class);
    }
}
