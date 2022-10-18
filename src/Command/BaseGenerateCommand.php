<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Command;

use ReflectionException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Yiisoft\Validator\Result;
use Yiisoft\Yii\Console\ExitCode;
use Yiisoft\Yii\Gii\CodeFile;
use Yiisoft\Yii\Gii\CodeFileWriteOperationEnum;
use Yiisoft\Yii\Gii\CodeFileWriter;
use Yiisoft\Yii\Gii\CodeFileWriteStatusEnum;
use Yiisoft\Yii\Gii\Exception\InvalidConfigException;
use Yiisoft\Yii\Gii\Exception\InvalidGeneratorCommandException;
use Yiisoft\Yii\Gii\Generator\AbstractGeneratorCommand;
use Yiisoft\Yii\Gii\GeneratorInterface;
use Yiisoft\Yii\Gii\GiiInterface;

use function count;

abstract class BaseGenerateCommand extends Command
{
    public function __construct(
        protected GiiInterface $gii,
        protected CodeFileWriter $codeFileWriter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('overwrite', 'o', InputArgument::OPTIONAL, '')
            ->addOption('template', 't', InputArgument::OPTIONAL, '');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var GeneratorInterface $generator */
        $generator = $this->getGenerator();
        $generatorCommand = $this->createGeneratorCommand($input);

        $output->writeln("Running '{$generator->getName()}'...\n");
        try {
            $files = $generator->generate($generatorCommand);
        } catch (InvalidGeneratorCommandException $e) {
            $this->displayValidationErrors($e->getResult(), $output);
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $this->generateCode($files, $generatorCommand, $input, $output);
        return ExitCode::OK;
    }

    abstract protected function getGenerator(): GeneratorInterface;

    protected function displayValidationErrors(Result $result, OutputInterface $output): void
    {
        $output->writeln("<fg=red>Code not generated. Please fix the following errors:</>\n");
        foreach ($result->getErrorMessages() as $attribute => $errorMessage) {
            $output->writeln(sprintf(' - <fg=cyan>%s</>: <fg=green>%s</>', $attribute, $errorMessage));
        }
        $output->writeln('');
    }

    /**
     * @param CodeFile[] $files
     * @param AbstractGeneratorCommand $generatorCommand
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws ReflectionException
     * @throws InvalidConfigException
     */
    protected function generateCode(
        array $files,
        AbstractGeneratorCommand $generatorCommand,
        InputInterface $input,
        OutputInterface $output
    ): void {
        if (count($files) === 0) {
            $output->writeln('<fg=cyan>No code to be generated.</>');
            return;
        }
        $output->writeln("<fg=magenta>The following files will be generated</>:\n");
        $skipAll = $input->isInteractive() ? null : !$input->getArgument('overwrite');
        $answers = [];
        foreach ($files as $file) {
            $path = $file->getRelativePath();
            if ($file->getOperation() === CodeFileWriteOperationEnum::OP_CREATE) {
                $output->writeln("    <fg=green>[new]</>       <fg=blue>$path</>");
                $answers[$file->getId()] = $file->getOperation()->value;
            } elseif ($file->getOperation() === CodeFileWriteOperationEnum::OP_SKIP) {
                $output->writeln("    <fg=green>[unchanged]</> <fg=blue>$path</>");
                $answers[$file->getId()] = $file->getOperation()->value;
            } else {
                $output->writeln("    <fg=green>[changed]</>   <fg=blue>$path</>");
                if ($skipAll !== null) {
                    $answers[$file->getId()] = CodeFileWriteOperationEnum::OP_OVERWRITE->value;
                } else {
                    $answer = $this->choice($input, $output);
                    $answers[$file->getId()] = ($answer === 'y' || $answer === 'ya')
                        ? CodeFileWriteOperationEnum::OP_OVERWRITE->value
                        : CodeFileWriteOperationEnum::OP_SKIP->value;
                    if ($answer === 'ya') {
                        $skipAll = false;
                    } elseif ($answer === 'na') {
                        $skipAll = true;
                    }
                }
            }
        }

//        if (!array_sum($answers)) {
//            $output->writeln("\n<fg=cyan>No files were chosen to be generated.</>");
//            return;
//        }

        if (!$this->confirm($input, $output)) {
            $output->writeln("\n<fg=cyan>No file was generated.</>");
            return;
        }

        $result = $this->codeFileWriter->write($files, $answers);

        $hasError = false;
        foreach ($result->getResults() as $fileId => $result) {
            $file = $files[$fileId];
            $color = match ($result['status']) {
                CodeFileWriteStatusEnum::CREATED->value => 'green',
                CodeFileWriteStatusEnum::OVERWROTE->value => 'blue',
                CodeFileWriteStatusEnum::ERROR->value => 'red',
                default => 'yellow',
            };
            $output->writeln(sprintf(
                "<fg=%s>%s</>: %s",
                $color,
                $result['status'],
                $file->getRelativePath(),
            ));
            if (CodeFileWriteStatusEnum::ERROR->value === $result['status']) {
                var_dump($result);
                exit();
                $hasError = true;
                $output->writeln(sprintf(
                    "<fg=red>%s</>",
                    $result['error']
                ));
            }
        }

        if ($hasError) {
            $output->writeln("\n<fg=red>Some errors occurred while generating the files.</>");
        } else {
            $output->writeln("\n<fg=green>Files were generated successfully!</>");
        }
    }

    abstract protected function createGeneratorCommand(InputInterface $input): AbstractGeneratorCommand;

    /**
     * @return bool|mixed|string|null
     */
    protected function confirm(InputInterface $input, OutputInterface $output)
    {
        $question = new ConfirmationQuestion("\nReady to generate the selected files? (yes|no) [yes]:", true);
        return $this->getHelper('question')->ask($input, $output, $question);
    }

    /**
     * @return bool|mixed|string|null
     */
    protected function choice(InputInterface $input, OutputInterface $output)
    {
        $question = new ChoiceQuestion(
            "\nDo you want to overwrite this file?",
            [
                'y' => 'Overwrite this file.',
                'n' => 'Skip this file.',
                'ya' => 'Overwrite this and the rest of the changed files.',
                'na' => 'Skip this and the rest of the changed files.',
            ]
        );
        return $this->getHelper('question')->ask($input, $output, $question);
    }
}
