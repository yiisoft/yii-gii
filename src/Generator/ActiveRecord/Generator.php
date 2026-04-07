<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\ActiveRecord;

use InvalidArgumentException;
use Throwable;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Schema\TableSchemaInterface;
use Yiisoft\Strings\Inflector;
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
        $relations = [];

        if ($schema = $this->connection->getTableSchema($command->tableName, true)) {
            foreach ($schema->getColumns() as $column) {
                $properties[(string) $column->getName()] = new Property($column);
            }

            // Generate relations if requested
            if ($command->generateRelations) {
                $relations = $this->generateRelations($command, $schema);

                // Mark columns used in relations
                foreach ($relations as $relation) {
                    foreach ($relation->link as $columnName) {
                        if (isset($properties[$columnName])) {
                            $properties[$columnName]->usedInRelation = true;
                        }
                    }
                }
            }
        }

        $path = $this->getControllerFile($command);
        $codeFile = (new CodeFile(
            $path,
            $this->render($command, 'model.php', [
                'properties' => $properties,
                'relations' => $relations,
            ])
        ))->withBasePath($rootPath);
        $files[$codeFile->getId()] = $codeFile;

        return $files;
    }

    /**
     * Generates relations based on foreign keys.
     *
     * @return array<Relation>
     */
    private function generateRelations(Command $command, TableSchemaInterface $schema): array
    {
        $relations = [];
        $inflector = new Inflector();

        // Get foreign keys from this table to other tables
        try {
            $foreignKeys = $schema->getForeignKeys();

            foreach ($foreignKeys as $fk) {
                $foreignTableName = $fk->foreignTableName;
                $relatedModelName = $inflector->tableToClass($foreignTableName);

                // Create relation name from foreign key columns
                $relationName = $this->generateRelationName($fk->columnNames, $relatedModelName);

                // Build link array [foreign_column => local_column]
                $link = [];
                foreach ($fk->columnNames as $index => $columnName) {
                    $foreignColumnName = $fk->foreignColumnNames[$index] ?? 'id';
                    $link[$foreignColumnName] = $columnName;
                }

                $relations[] = new Relation(
                    name: $relationName,
                    relatedModel: $relatedModelName,
                    type: 'hasOne',
                    link: $link,
                    inverseOf: strtolower($inflector->toPlural($command->getModelName())),
                );
            }
        } catch (Throwable) {
            // If we can't get foreign keys, just skip relations
        }

        return $relations;
    }

    /**
     * Generates a relation name from foreign key columns.
     *
     * @param string[] $columns
     */
    private function generateRelationName(array $columns, string $relatedModelName): string
    {
        $inflector = new Inflector();

        // If the FK column is like 'user_id', use 'user'
        // If the FK column is like 'profile_id', use 'profile'
        if (count($columns) === 1) {
            $column = $columns[0];
            if (str_ends_with($column, '_id')) {
                return substr($column, 0, -3);
            }
        }

        // Default to using the related model name in camelCase
        return lcfirst($relatedModelName);
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
