<?php

namespace Yiisoft\Yii\Gii\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Yii\Gii\GiiInterface;

abstract class BaseGenerateCommand extends Command
{
    protected const NAME = '';

    /**
     * @var \Yiisoft\Yii\Gii\GeneratorInterface
     */
    protected $generator;

    public function __construct(GiiInterface $gii)
    {
        parent::__construct();
        $this->generator = $gii->getGenerator(self::NAME);
    }

    protected function configure(): void
    {
        $this->addArgument('interactive', InputArgument::OPTIONAL, '', true)
             ->addArgument('overwrite', InputArgument::OPTIONAL, '', false);
    }

    /**
     * @param  InputInterface  $input
     * @param  OutputInterface  $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        echo "Running '{$this->generator->getName()}'...\n\n";
        /*if ($this->generator->validate()) {
            $this->generateCode($input, $output);
        } else {
            $this->displayValidationErrors($output);
        }*/
    }

    protected function displayValidationErrors(OutputInterface $output)
    {
        $output->writeln("Code not generated. Please fix the following errors:\n\n", Console::FG_RED);
        foreach ($this->generator->errors as $attribute => $errors) {
            echo ' - '.$this->controller->ansiFormat($attribute, Console::FG_CYAN).': '.implode('; ', $errors)."\n";
        }
        echo "\n";
    }

    protected function generateCode(InputInterface $input, OutputInterface $output)
    {
        $files = $this->generator->generate();
        $n     = count($files);
        if ($n === 0) {
            echo "No code to be generated.\n";
            return;
        }
        echo "The following files will be generated:\n";
        $skipAll = $input->getArgument('interactive') ? null : !$input->getArgument('overwrite');
        $answers = [];
        foreach ($files as $file) {
            $path = $file->getRelativePath();
            if (is_file($file->getPath())) {
                if (file_get_contents($file->getPath()) === $file->getContent()) {
                    echo '  '.$this->controller->ansiFormat('[unchanged]', Console::FG_GREY);
                    echo $this->controller->ansiFormat(" $path\n", Console::FG_CYAN);
                    $answers[$file->getId()] = false;
                } else {
                    echo '    '.$this->controller->ansiFormat('[changed]', Console::FG_RED);
                    echo $this->controller->ansiFormat(" $path\n", Console::FG_CYAN);
                    if ($skipAll !== null) {
                        $answers[$file->getId()] = !$skipAll;
                    } else {
                        $answer                  = $this->controller->select(
                            "Do you want to overwrite this file?",
                            [
                                'y'  => 'Overwrite this file.',
                                'n'  => 'Skip this file.',
                                'ya' => 'Overwrite this and the rest of the changed files.',
                                'na' => 'Skip this and the rest of the changed files.',
                            ]
                        );
                        $answers[$file->getId()] = $answer === 'y' || $answer === 'ya';
                        if ($answer === 'ya') {
                            $skipAll = false;
                        } elseif ($answer === 'na') {
                            $skipAll = true;
                        }
                    }
                }
            } else {
                echo '        '.$this->controller->ansiFormat('[new]', Console::FG_GREEN);
                echo $this->controller->ansiFormat(" $path\n", Console::FG_CYAN);
                $answers[$file->getId()] = true;
            }
        }

        if (!array_sum($answers)) {
            $this->controller->stdout("\nNo files were chosen to be generated.\n", Console::FG_CYAN);
            return;
        }

        if (!$this->controller->confirm("\nReady to generate the selected files?", true)) {
            $this->controller->stdout("\nNo file was generated.\n", Console::FG_CYAN);
            return;
        }

        if ($this->generator->save($files, (array)$answers, $results)) {
            $this->controller->stdout("\nFiles were generated successfully!\n", Console::FG_GREEN);
        } else {
            $this->controller->stdout("\nSome errors occurred while generating the files.", Console::FG_RED);
        }
        echo preg_replace('%<span class="error">(.*?)</span>%', '\1', $results)."\n";
    }
}
