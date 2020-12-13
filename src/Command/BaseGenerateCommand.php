<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Yiisoft\Yii\Console\ExitCode;
use Yiisoft\Yii\Gii\CodeFile;
use Yiisoft\Yii\Gii\Generator\AbstractGenerator;
use Yiisoft\Yii\Gii\GeneratorInterface;
use Yiisoft\Yii\Gii\GiiInterface;

use function count;

abstract class BaseGenerateCommand extends Command
{
    protected GiiInterface $gii;

    public function __construct(GiiInterface $gii)
    {
        parent::__construct();
        $this->gii = $gii;
    }

    protected function configure(): void
    {
        $this->addOption('overwrite', 'o', InputArgument::OPTIONAL, '')
            ->addOption('template', 't', InputArgument::OPTIONAL, '');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var AbstractGenerator $generator */
        $generator = $this->getGenerator();
        $generator->load(array_filter(array_merge($input->getOptions(), $input->getArguments())));
        $output->writeln("Running '{$generator->getName()}'...\n");
        if ($generator->validate() && !$generator->hasErrors()) {
            $this->generateCode($generator, $input, $output);
        } else {
            $this->displayValidationErrors($generator, $output);
            return ExitCode::UNSPECIFIED_ERROR;
        }
        return ExitCode::OK;
    }

    abstract protected function getGenerator(): GeneratorInterface;

    protected function displayValidationErrors(GeneratorInterface $generator, OutputInterface $output): void
    {
        $output->writeln("<fg=red>Code not generated. Please fix the following errors:</>\n");
        /** @var AbstractGenerator $generator */
        foreach ($generator->getErrors() as $attribute => $errors) {
            $output->writeln(sprintf(' - <fg=cyan>%s</>: <fg=green>%s</>', $attribute, implode('; ', $errors)));
        }
        $output->writeln('');
    }

    protected function generateCode(GeneratorInterface $generator, InputInterface $input, OutputInterface $output): void
    {
        /** @var AbstractGenerator $generator */
        $files = $generator->generate();
        if (count($files) === 0) {
            $output->writeln('<fg=cyan>No code to be generated.</>');
            return;
        }
        $output->writeln("<fg=magenta>The following files will be generated</>:\n");
        $skipAll = $input->isInteractive() ? null : !$input->getArgument('overwrite');
        $answers = [];
        foreach ($files as $file) {
            $path = $file->getRelativePath();
            if ($file->getOperation() === CodeFile::OP_CREATE) {
                $output->writeln("    <fg=green>[new]</>       <fg=blue>$path</>");
                $answers[$file->getId()] = true;
            } elseif ($file->getOperation() === CodeFile::OP_SKIP) {
                $output->writeln("    <fg=green>[unchanged]</> <fg=blue>$path</>");
                $answers[$file->getId()] = false;
            } else {
                $output->writeln("    <fg=green>[changed]</>   <fg=blue>$path</>");
                if ($skipAll !== null) {
                    $answers[$file->getId()] = !$skipAll;
                } else {
                    $answer = $this->choice($input, $output);
                    $answers[$file->getId()] = $answer === 'y' || $answer === 'ya';
                    if ($answer === 'ya') {
                        $skipAll = false;
                    } elseif ($answer === 'na') {
                        $skipAll = true;
                    }
                }
            }
        }

        if (!array_sum($answers)) {
            $output->writeln("\n<fg=cyan>No files were chosen to be generated.</>");
            return;
        }

        if (!$this->confirm($input, $output)) {
            $output->writeln("\n<fg=cyan>No file was generated.</>");
            return;
        }

        $results = [];
        $isSaved = $generator->save($files, $answers, $results);
        foreach ($results as $n => $result) {
            if ($n === 0) {
                $output->writeln("<fg=blue>{$result}</>");
            } elseif ($n === key(array_slice($results, -1, 1, true))) {
                $output->writeln("<fg=green>{$result}</>");
            } else {
                $output->writeln(
                    '<fg=yellow>' . preg_replace(
                        '%<span class="error">(.*?)</span>%',
                        '<fg=red>\1</>',
                        $result
                    ) . '</>'
                );
            }
        }

        if ($isSaved) {
            $output->writeln("\n<fg=green>Files were generated successfully!</>");
        } else {
            $output->writeln("\n<fg=red>Some errors occurred while generating the files.</>");
        }
    }

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
