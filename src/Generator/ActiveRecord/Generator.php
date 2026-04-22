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
        $schema = $this->connection->getTableSchema($command->table, true);

        if ($schema !== null) {
            foreach ($schema->getColumns() as $columnName => $column) {
                $properties[$columnName] = new Property($column);
            }

            if ($command->generateRelations) {
                // Generate outgoing relations (this table's FKs to other tables)
                foreach ($schema->getForeignKeys() as $foreignKey) {
                    $relations[] = new Relation($foreignKey, $command->getModelName());
                }

                // Generate inverse relations (other tables' FKs to this table)
                $inverseRelations = $this->findInverseRelations($command->table, $command->getModelName());
                foreach ($inverseRelations as $inverseRelation) {
                    $relations[] = $inverseRelation;
                }

                // Remove duplicate relation names (keep first occurrence)
                $relations = $this->deduplicateRelations($relations);

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
            ])
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
    private function findInverseRelations(string $currentTable, string $modelName): array
    {
        $inverseRelations = [];
        $allTableNames = $this->connection->getSchema()->getTableNames();

        foreach ($allTableNames as $tableName) {
            // Skip the current table (already processed its outgoing FKs)
            if ($tableName === $currentTable) {
                continue;
            }

            $tableSchema = $this->connection->getTableSchema($tableName, true);
            if ($tableSchema === null) {
                continue;
            }

            // Check each FK in this table to see if it references our current table
            foreach ($tableSchema->getForeignKeys() as $foreignKey) {
                if ($foreignKey->foreignTableName === $currentTable) {
                    // This FK points to our table, so we need an inverse relation
                    $isUnique = $this->isForeignKeyUnique($tableSchema, $foreignKey);
                    $inverseRelations[] = new InverseRelation(
                        $foreignKey,
                        $tableName,
                        $modelName,
                        $isUnique
                    );
                }
            }
        }

        return $inverseRelations;
    }

    /**
     * Remove duplicate relations with the same name.
     * Keeps the first occurrence (outgoing relations take precedence over inverse relations).
     *
     * @param list<InverseRelation|Relation> $relations
     * @return list<InverseRelation|Relation>
     */
    private function deduplicateRelations(array $relations): array
    {
        $seen = [];
        $deduplicated = [];

        foreach ($relations as $relation) {
            $name = $relation->getName();
            if (!isset($seen[$name])) {
                $seen[$name] = true;
                $deduplicated[] = $relation;
            }
        }

        return $deduplicated;
    }

    /**
     * Check if the foreign key columns form a unique constraint.
     * If they do, this should be a hasOne relation; otherwise hasMany.
     *
     * @param \Yiisoft\Db\Schema\TableSchema $tableSchema
     * @param \Yiisoft\Db\Constraint\ForeignKey $foreignKey
     */
    private function isForeignKeyUnique($tableSchema, $foreignKey): bool
    {
        $fkColumns = $foreignKey->columnNames;
        sort($fkColumns);

        // Check primary key
        $primaryKey = $tableSchema->getPrimaryKey();
        if ($primaryKey !== null && is_array($primaryKey)) {
            $pkColumns = $primaryKey;
            sort($pkColumns);
            if ($fkColumns === $pkColumns) {
                return true;
            }
        }

        // Check unique indexes
        foreach ($tableSchema->getIndexes() as $index) {
            if (is_object($index) && isset($index->isUnique) && $index->isUnique) {
                $indexColumns = is_array($index->columnNames) ? $index->columnNames : $index;
                if (is_array($indexColumns)) {
                    sort($indexColumns);
                    if ($fkColumns === $indexColumns) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
