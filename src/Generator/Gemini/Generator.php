<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\Gemini;

use Gemini;
use InvalidArgumentException;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Validator\ValidatorInterface;
use Yiisoft\Yii\Gii\Component\CodeFile\CodeFile;
use Yiisoft\Yii\Gii\Generator\AbstractGenerator;
use Yiisoft\Yii\Gii\GeneratorCommandInterface;
use Yiisoft\Yii\Gii\ParametersProvider;

/**
 * This generator will generate a controller and one or a few action view files.
 */
final class Generator extends AbstractGenerator
{
    public function __construct(
        Aliases $aliases,
        ValidatorInterface $validator,
        ParametersProvider $parametersProvider,
    ) {
        parent::__construct($aliases, $validator, $parametersProvider);
    }

    public static function getId(): string
    {
        return 'ai-gemini';
    }

    public static function getName(): string
    {
        return 'AI / Gemini';
    }

    public static function getDescription(): string
    {
        return '';
    }

    public function getRequiredTemplates(): array
    {
        return [
            'model.php',
        ];
    }

    public function doGenerate(GeneratorCommandInterface $command): array
    {
        if (!$command instanceof Command) {
            throw new InvalidArgumentException();
        }

        $apiKey = getenv('GEMINI_API_KEY');
        $client = Gemini::client($apiKey);

        //$prompt = $command->getPrompt();
        $prompt = [
            "Generate a test with PHPUnit for the following class",
            file_get_contents(__DIR__ . '/../../Gii.php'),
        ];
        $result = $client->geminiPro()->generateContent($prompt);

        $rootPath = $this->aliases->get('@root');

        $path = $this->getOutputFile($command);
        $fileContent = preg_replace(
            '/^(```[a-z]+)\r?\n?|\r?\n?```$/i',
            '',
            $result->text(),
        );
        $codeFile = (new CodeFile(
            $path,
            $fileContent,
        ))->withBasePath($rootPath);

        $files = [];
        $files[$codeFile->getId()] = $codeFile;

        return $files;
    }

    /**
     * @return string the controller class file path
     */
    private function getOutputFile(Command $command): string
    {
        $directory = '@root/';

        return $this->aliases->get(
            str_replace(
                ['\\', '//'],
                '/',
                sprintf(
                    '%s/%s',
                    $directory,
                    $command->getResultFilePath(),
                ),
            ),
        );
    }

    public static function getCommandClass(): string
    {
        return Command::class;
    }
}
