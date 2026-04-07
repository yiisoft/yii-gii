<?php

declare(strict_types=1);

use Yiisoft\Strings\StringHelper;
use Yiisoft\Yii\Gii\Generator\ActiveRecord\Column;
use Yiisoft\Yii\Gii\Generator\ActiveRecord\Relation;

/**
 * @var Yiisoft\Yii\Gii\Generator\ActiveRecord\Command $command
 * @var list<Column> $properties
 * @var list<Relation> $relations
 */

echo "<?php\n";
?>

declare(strict_types=1);

namespace <?= $command->namespace ?>;

use <?= $command->baseClass ?>;
<?php if ($command->useRepositoryTrait): ?>
use Yiisoft\ActiveRecord\Trait\RepositoryTrait;
<?php endif; ?>
<?php if ($command->usePrivatePropertiesTrait()): ?>
use Yiisoft\ActiveRecord\Trait\PrivatePropertiesTrait;
<?php endif; ?>
<?php if ($command->generateRelations && !empty($relations)): ?>
use Yiisoft\ActiveRecord\ActiveQueryInterface;
<?php endif; ?>
<?php
// Collect unique related model names for use statements
$relatedModels = [];
foreach ($relations as $relation) {
    $relatedModels[$relation->relatedModel] = true;
}
foreach (array_keys($relatedModels) as $relatedModel):
?>
use <?= $command->namespace . '\\' . $relatedModel ?>;
<?php endforeach; ?>

final class <?= $command->getModelName(); ?> extends <?= StringHelper::baseName($command->baseClass) . PHP_EOL ?>
{
<?php if ($command->useRepositoryTrait): ?>
    use RepositoryTrait;
<?php endif; ?>
<?php if ($command->usePrivatePropertiesTrait()): ?>
    use PrivatePropertiesTrait;
<?php endif; ?>
<?php if ($command->useRepositoryTrait || $command->usePrivatePropertiesTrait()): ?>

<?php endif; ?>
<?php foreach ($properties as $property): ?>
    <?= $command->propertyVisibility ?> <?=sprintf(
        '%s%s $%s%s',
        $property->isAllowNull ? '?' : '',
        $property->type,
        $property->name,
        $property->isDefaultValueBuiltinType() ? ' = ' . $property->getPhpDefaultValue() : '',
    )?>;
<?php endforeach; ?>
<?php if (!empty($properties)): ?>

<?php endif; ?>
<?php
// Check if we need a constructor for DB expression initialization
$needsConstructor = false;
foreach ($properties as $property) {
    if ($property->isDefaultValueExpression()) {
        $needsConstructor = true;
        break;
    }
}
?>
<?php if ($needsConstructor): ?>
    public function __construct()
    {
<?php foreach ($properties as $property): ?>
<?php if ($property->isDefaultValueExpression()): ?>
        $this-><?= $property->name ?> = <?= $property->getDbExpressionInitializer() ?>;
<?php endif; ?>
<?php endforeach; ?>
    }

<?php endif; ?>
    public function tableName(): string
    {
        return '<?= $command->tableName ?>';
    }
<?php if ($command->generateGettersSetters): ?>
<?php foreach ($properties as $property): ?>

    public function get<?= $property->getPascalCaseName() ?>(): <?= ($property->isAllowNull || $property->canBeUninitialized() ? '?' : '') . $property->type . PHP_EOL ?>
    {
<?php if ($property->shouldUseNullCoalescing()): ?>
        return $this-><?= $property->name ?> ?? null;
<?php else: ?>
        return $this-><?= $property->name ?>;
<?php endif; ?>
    }

    public function set<?= $property->getPascalCaseName() ?>(<?= ($property->isAllowNull ? '?' : '') . $property->type ?> $<?= $property->name ?>): void
    {
<?php if ($property->shouldUseSetMethod()): ?>
        $this->set('<?= $property->name ?>', $<?= $property->name ?>);
<?php else: ?>
        $this-><?= $property->name ?> = $<?= $property->name ?>;
<?php endif; ?>
    }
<?php endforeach; ?>
<?php endif; ?>
<?php if ($command->generateRelations && !empty($relations)): ?>

    public function relationQuery(string $name): ActiveQueryInterface
    {
        return match ($name) {
<?php foreach ($relations as $relation): ?>
            '<?= $relation->name ?>' => $this-><?= $relation->getQueryMethodName() ?>(),
<?php endforeach; ?>
            default => parent::relationQuery($name),
        };
    }
<?php foreach ($relations as $relation): ?>

    public function <?= $relation->getGetterMethodName() ?>(): <?= $relation->getGetterReturnType() . PHP_EOL ?>
    {
        return $this->relation('<?= $relation->name ?>');
    }

    public function <?= $relation->getQueryMethodName() ?>(): ActiveQueryInterface
    {
        return $this-><?= $relation->isHasOne() ? 'hasOne' : 'hasMany' ?>(<?= $relation->relatedModel ?>::class, <?= var_export($relation->link, true) ?>)<?= $relation->inverseOf !== null ? "->inverseOf('" . $relation->inverseOf . "')" : '' ?>;
    }
<?php endforeach; ?>
<?php endif; ?>
}
