<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Yiisoft\Yii\Console\ExitCode;
use Yiisoft\Yii\Gii\Command\ActiveRecordCommand;
use Yiisoft\Yii\Gii\Component\CodeFile\CodeFileWriter;
use Yiisoft\Yii\Gii\GiiInterface;
use Yiisoft\Yii\Gii\Tests\TestCase;

final class ActiveRecordCommandTest extends TestCase
{
    public function testExecuteWithValidTable(): void
    {
        $commandTester = $this->createCommandTester();

        $commandTester->execute(
            ['tableName' => 'user'],
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
            ['tableName' => 'non_existent_table'],
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
                'tableName' => 'user',
                '--namespace' => 'App\\Entity',
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
                'tableName' => 'user',
                '--baseClass' => 'Yiisoft\\ActiveRecord\\ActiveRecord',
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
                'tableName' => 'user',
                '--propertyVisibility' => 'private',
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
                'tableName' => 'user',
                '--generateGettersSetters' => 'false',
                '--generateRelations' => 'false',
                '--useRepositoryTrait' => 'true',
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
                'tableName' => 'user',
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
                'tableName' => 'user',
                '--namespace' => 'App\\Model',
                '--baseClass' => 'Yiisoft\\ActiveRecord\\ActiveRecord',
                '--propertyVisibility' => 'protected',
                '--generateGettersSetters' => 'true',
                '--generateRelations' => 'true',
                '--useRepositoryTrait' => 'false',
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
                'tableName' => 'user',
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

        $this->assertSame('gii/active-record', $command->getName());
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
