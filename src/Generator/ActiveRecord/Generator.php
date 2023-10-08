<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\ActiveRecord;

use InvalidArgumentException;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Db\Connection\ConnectionInterface;
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
        private readonly ConnectionInterface $connection,
    ) {
        parent::__construct($aliases, $validator, $parametersProvider);
    }

    public static function getId(): string
    {
        return 'active-record';
    }

    public static function getName(): string
    {
        return 'Active Record';
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

        $files = [];

        $rootPath = $this->aliases->get('@root');

        $properties = [];
        if ($schema = $this->connection->getTableSchema($command->getTableName(), true)) {
            foreach ($schema->getColumns() as $columnSchema) {
                $properties[] = [
                    'name' => $columnSchema->getName(),
                    'type' => match ($columnSchema->getPhpType()) {
                        'integer' => 'int',
                        default => 'string',
                    },
                    'isAllowNull' => $columnSchema->isAllowNull(),
                    'defaultValue' => $columnSchema->getDefaultValue(),
                ];
            }
        }
        $path = $this->getControllerFile($command);
        $codeFile = (new CodeFile(
            $path,
            $this->render($command, 'model.php', ['properties' => $properties])
        ))->withBasePath($rootPath);
        $files[$codeFile->getId()] = $codeFile;

        return $files;
    }

    /**
     * @return string the controller class file path
     */
    private function getControllerFile(Command $command): string
    {
        $directory = '@src/Model/';

        return $this->aliases->get(
            str_replace(
                ['\\', '//'],
                '/',
                sprintf(
                    '%s/%s.php',
                    $directory,
                    $command->getModelName(),
                ),
            ),
        );
    }

    public static function getCommandClass(): string
    {
        return Command::class;
    }
}
