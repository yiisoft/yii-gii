<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\ActiveRecord;

use InvalidArgumentException;
use ReflectionNamedType;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Constraint\ForeignKeyConstraint;
use Yiisoft\Db\Expression\ExpressionInterface;
use Yiisoft\Db\Schema\Column\ColumnInterface;
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

    /**
     * @psalm-suppress DocblockTypeContradiction 'integer' => 'int'
     * @psalm-suppress DeprecatedMethod $columnSchema->isAllowNull()
     */
    public function doGenerate(GeneratorCommandInterface $command): array
    {
        if (!$command instanceof Command) {
            throw new InvalidArgumentException();
        }

        $files = [];

        $rootPath = $this->aliases->get('@root');

        $properties = [];
        $relations = [];

        if ($schema = $this->connection->getTableSchema($command->getTableName(), true)) {
            $primaryKeys = $schema->getPrimaryKey();

            // First pass: create columns
            $columnsMap = [];
            foreach ($schema->getColumns() as $columnSchema) {
                $columnName = (string)$columnSchema->getName();
                $phpType = $this->getPhpType($columnSchema);
                $defaultValue = $columnSchema->getDefaultValue();

                // Check if the column has a DB default expression (like CURRENT_TIMESTAMP, NOW(), etc.)
                $hasDbDefaultExpression = $this->hasDbDefaultExpression($columnSchema);

                $isPrimaryKey = in_array($columnName, $primaryKeys, true);

                $column = new Column(
                    name: $columnName,
                    type: $phpType,
                    // Primary keys are never NULL, even if not explicitly marked as NOT NULL
                    isAllowNull: !$isPrimaryKey && !$columnSchema->isNotNull(),
                    defaultValue: $defaultValue,
                    isPrimaryKey: $isPrimaryKey,
                    isAutoIncrement: $columnSchema->isAutoIncrement(),
                    hasDbDefaultExpression: $hasDbDefaultExpression,
                    isUsedInRelation: false,
                );

                $columnsMap[$columnName] = $column;
                $properties[] = $column;
            }

            // Generate relations if requested
            if ($command->isGenerateRelations()) {
                $relations = $this->generateRelations($command, $schema);

                // Mark columns used in relations
                foreach ($relations as $relation) {
                    foreach ($relation->link as $foreignColumn => $localColumn) {
                        if (isset($columnsMap[$localColumn])) {
                            $columnsMap[$localColumn]->isUsedInRelation = true;
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
     * Gets the PHP type for a column using reflection of phpTypecast() return type.
     */
    private function getPhpType(ColumnInterface $columnSchema): string
    {
        try {
            $reflection = new \ReflectionMethod($columnSchema, 'phpTypecast');
            $returnType = $reflection->getReturnType();

            if ($returnType instanceof ReflectionNamedType) {
                $typeName = $returnType->getName();

                // Map PHP type names to their short forms
                return match ($typeName) {
                    'integer', 'int' => 'int',
                    'boolean', 'bool' => 'bool',
                    'double', 'float' => 'float',
                    'string' => 'string',
                    'array' => 'array',
                    default => 'mixed',
                };
            }

            // Handle union types (e.g., int|string|null)
            if ($returnType instanceof \ReflectionUnionType) {
                $types = [];
                foreach ($returnType->getTypes() as $type) {
                    if ($type instanceof ReflectionNamedType) {
                        $types[] = $type->getName();
                    }
                }

                // If union contains null, filter it out for now and mark as nullable
                $types = array_filter($types, fn($t) => $t !== 'null');

                // Return the first non-null type, or 'mixed' if multiple non-null types
                if (count($types) === 1) {
                    $typeName = reset($types);
                    return match ($typeName) {
                        'integer', 'int' => 'int',
                        'boolean', 'bool' => 'bool',
                        'double', 'float' => 'float',
                        'string' => 'string',
                        'array' => 'array',
                        default => 'mixed',
                    };
                }

                return 'mixed';
            }

            // Handle intersection types (e.g., Countable&Iterator)
            if ($returnType instanceof \ReflectionIntersectionType) {
                // For intersection types, we can't represent them simply, use 'mixed'
                return 'mixed';
            }
        } catch (\ReflectionException) {
            // If reflection fails, default to 'string'
        }

        return 'string';
    }

    /**
     * Checks if a column has a database default expression.
     */
    private function hasDbDefaultExpression(ColumnInterface $columnSchema): bool
    {
        $defaultValue = $columnSchema->getDefaultValue();

        // Check if default value is an expression object
        return $defaultValue instanceof ExpressionInterface;
    }

    /**
     * Generates relations based on foreign keys.
     *
     * @return array<Relation>
     */
    private function generateRelations(Command $command, $schema): array
    {
        $relations = [];
        $inflector = new Inflector();
        $tableName = $command->getTableName();

        // Get foreign keys from this table to other tables
        try {
            $foreignKeys = $schema->getForeignKeys();

            foreach ($foreignKeys as $fk) {
                if (!$fk instanceof ForeignKeyConstraint) {
                    continue;
                }

                $foreignTableName = $fk->getForeignTableName();
                $relatedModelName = $inflector->tableToClass($foreignTableName);
                $relatedModelClass = $command->getNamespace() . '\\' . $relatedModelName;

                // Create relation name from foreign key columns
                $relationName = $this->generateRelationName($fk->getColumnNames(), $relatedModelName);

                // Build link array [foreign_column => local_column]
                $link = [];
                foreach ($fk->getColumnNames() as $index => $columnName) {
                    $foreignColumnName = $fk->getForeignColumnNames()[$index] ?? 'id';
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
        } catch (\Throwable) {
            // If we can't get foreign keys, just skip relations
        }

        return $relations;
    }

    /**
     * Generates a relation name from foreign key columns.
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
