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
use Yiisoft\Yii\Gii\Helper;
use Yiisoft\Yii\Gii\ParametersProvider;

use Throwable;

use function sprintf;

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

    public static function getCommandClass(): string
    {
        return Command::class;
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
        $relations = [];
        $schema = $this->connection->getTableSchema($command->table);

        if ($schema !== null) {
            foreach ($schema->getColumns() as $columnName => $column) {
                $properties[$columnName] = new Property($column);
            }

            if ($command->generateRelations) {
                // Generate outgoing relations (this table's FKs to other tables)
                foreach ($schema->getForeignKeys() as $foreignKey) {
                    $relation = new Relation($foreignKey, $command->getModelName());
                    $relations[$relation->getName()] = $relation;
                }

                // Generate inverse relations (other tables' FKs to this table)
                $inverseRelations = $this->findInverseRelations($command->table);
                foreach ($inverseRelations as $inverseRelation) {
                    $inverseRelationName = $inverseRelation->getName();

                    if (isset($relations[$inverseRelationName])) {
                        continue;
                    }

                    $relations[$inverseRelationName] = $inverseRelation;
                }

                // Mark columns used in relations
                foreach ($relations as $relation) {
                    foreach ($relation->getLink() as $columnName) {
                        if (isset($properties[$columnName])) {
                            $properties[$columnName]->usedInRelation = true;
                        }
                    }
                }
            }
        }

        $path = $this->getModelFile($command);
        $codeFile = (new CodeFile(
            $path,
            $this->render($command, 'model.php', [
                'properties' => $properties,
                'relations' => $relations,
            ]),
        ))->withBasePath($rootPath);
        $files[$codeFile->getId()] = $codeFile;

        return $files;
    }

    /**
     * @return string the model class file path
     */
    private function getModelFile(Command $command): string
    {
        $directory = Helper::getNamespacePath($command->namespace);

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

    /**
     * Find inverse relations by scanning other tables for FKs that reference the current table.
     *
     * @return list<InverseRelation>
     */
    private function findInverseRelations(string $currentTable): array
    {
        $inverseRelations = [];
        $allTableNames = $this->connection->getSchema()->getTableNames();

        foreach ($allTableNames as $tableName) {
            if ($tableName === $currentTable) {
                continue;
            }

            $tableSchema = $this->connection->getTableSchema($tableName);
            if ($tableSchema === null) {
                continue;
            }

            foreach ($tableSchema->getForeignKeys() as $foreignKey) {
                if ($foreignKey->foreignTableName === $currentTable) {
                    $inverseRelations[] = new InverseRelation($foreignKey, $tableName);
                }
            }
        }

        return $inverseRelations;
    }
}
