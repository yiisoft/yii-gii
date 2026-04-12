<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\Files\FileHelper;
use Yiisoft\Yii\Console\ExitCode;
use Yiisoft\Yii\Gii\Command\ActiveRecordCommand;
use Yiisoft\Yii\Gii\Component\CodeFile\CodeFileWriter;
use Yiisoft\Yii\Gii\GiiInterface;
use Yiisoft\Yii\Gii\Tests\TestCase;

use function file_exists;

final class ActiveRecordCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $file = __DIR__ . '/../Model/User.php';

        if (file_exists($file)) {
            FileHelper::unlink($file);
        }
    }

    public function testExecuteWithValidTable(): void
    {
        $commandTester = $this->createCommandTester();

        $commandTester->execute(
            [
                'table' => 'user',
                '--namespace' => 'Yiisoft\\Yii\\Gii\\Tests\\Model',
            ],
            ['interactive' => false],
        );

        $commandTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Running', $commandTester->getDisplay());
        $this->assertStringContainsString('Files were generated successfully!', $commandTester->getDisplay());
    }

    public function testExecuteWithInvalidTable(): void
    {
        $commandTester = $this->createCommandTester();

        $exitCode = $commandTester->execute(
            ['table' => 'non_existent_table'],
            ['interactive' => false],
        );

        $this->assertSame(ExitCode::UNSPECIFIED_ERROR, $exitCode);
        $this->assertStringContainsString('Code not generated', $commandTester->getDisplay());
    }

    public function testExecuteWithNamespaceOption(): void
    {
        $commandTester = $this->createCommandTester();

        $commandTester->execute(
            [
                'table' => 'user',
                '--namespace' => 'Yiisoft\\Yii\\Gii\\Tests\\Model',
            ],
            ['interactive' => false],
        );

        $commandTester->assertCommandIsSuccessful();
    }

    public function testExecuteWithBaseClassOption(): void
    {
        $commandTester = $this->createCommandTester();

        $commandTester->execute(
            [
                'table' => 'user',
                '--base' => ActiveRecord::class,
                '--namespace' => 'Yiisoft\\Yii\\Gii\\Tests\\Model',
            ],
            ['interactive' => false],
        );

        $commandTester->assertCommandIsSuccessful();
    }

    public function testExecuteWithPropertyVisibilityOption(): void
    {
        $commandTester = $this->createCommandTester();

        $commandTester->execute(
            [
                'table' => 'user',
                '--namespace' => 'Yiisoft\\Yii\\Gii\\Tests\\Model',
                '--visibility' => 'private',
            ],
            ['interactive' => false],
        );

        $commandTester->assertCommandIsSuccessful();
    }

    public function testExecuteWithBooleanOptions(): void
    {
        $commandTester = $this->createCommandTester();

        $commandTester->execute(
            [
                'table' => 'user',
                '--namespace' => 'Yiisoft\\Yii\\Gii\\Tests\\Model',
                '--no-get-set' => true,
                '--no-relations' => true,
                '--repository' => true,
            ],
            ['interactive' => false],
        );

        $commandTester->assertCommandIsSuccessful();
    }

    public function testExecuteWithTemplateOption(): void
    {
        $commandTester = $this->createCommandTester();

        $commandTester->execute(
            [
                'table' => 'user',
                '--namespace' => 'Yiisoft\\Yii\\Gii\\Tests\\Model',
                '--template' => 'default',
            ],
            ['interactive' => false],
        );

        $commandTester->assertCommandIsSuccessful();
    }

    public function testExecuteWithAllOptions(): void
    {
        $commandTester = $this->createCommandTester();

        $commandTester->execute(
            [
                'table' => 'user',
                '--namespace' => 'Yiisoft\\Yii\\Gii\\Tests\\Model',
                '--base' => ActiveRecord::class,
                '--visibility' => 'protected',
                '--no-get-set' => true,
                '--no-relations' => true,
                '--repository' => true,
                '--template' => 'default',
            ],
            ['interactive' => false],
        );

        $commandTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Files were generated successfully!', $commandTester->getDisplay());
    }

    public function testExecuteWithInvalidNamespace(): void
    {
        $commandTester = $this->createCommandTester();

        $exitCode = $commandTester->execute(
            [
                'table' => 'user',
                '--namespace' => '0Invalid',
            ],
            ['interactive' => false],
        );

        $this->assertSame(ExitCode::UNSPECIFIED_ERROR, $exitCode);
        $this->assertStringContainsString('Code not generated', $commandTester->getDisplay());
    }

    public function testCommandName(): void
    {
        $container = $this->getContainer();
        $command = new ActiveRecordCommand(
            $container->get(GiiInterface::class),
            $container->get(CodeFileWriter::class),
        );

        $this->assertSame('gii:active-record', $command->getName());
    }

    public function testCommandDescription(): void
    {
        $container = $this->getContainer();
        $command = new ActiveRecordCommand(
            $container->get(GiiInterface::class),
            $container->get(CodeFileWriter::class),
        );

        $this->assertSame('Gii ActiveRecord model generator', $command->getDescription());
    }

    private function createCommandTester(): CommandTester
    {
        $container = $this->getContainer();
        $command = new ActiveRecordCommand(
            $container->get(GiiInterface::class),
            $container->get(CodeFileWriter::class),
        );

        return new CommandTester($command);
    }
}
