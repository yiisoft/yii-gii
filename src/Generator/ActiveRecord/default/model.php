<?php

declare(strict_types=1);

use Yiisoft\Strings\StringHelper;
use Yiisoft\Yii\Gii\Generator\ActiveRecord\Property;
use Yiisoft\Yii\Gii\Generator\ActiveRecord\Relation;

/**
 * @var Yiisoft\Yii\Gii\Generator\ActiveRecord\Command $command
 * @var list<Property> $properties
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
    $relatedModels[$relation->getRelatedModel()] = true;
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
    <?= sprintf(
        '%s %s $%s%s',
        $command->propertyVisibility,
        $property->getType(),
        $property->getName(),
        $property->isDefaultValueConstant() ? ' = ' . $property->getDefaultValueConstant() : '',
    ) ?>;
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
        $this-><?= $property->getName() ?> = <?= $property->getDbExpressionInitializer() ?>;
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

    public function get<?= $property->getPascalCaseName() ?>(): <?= $property->getReturnType() . PHP_EOL ?>
    {
<?php if ($property->isUninitialized()): ?>
        return $this-><?= $property->getName() ?> ?? null;
<?php else: ?>
        return $this-><?= $property->getName() ?>;
<?php endif; ?>
    }

    public function set<?= $property->getPascalCaseName() ?>(<?= $property->getType() ?> $<?= $property->getName() ?>): void
    {
<?php if ($property->shouldUseSetMethod()): ?>
        $this->set('<?= $property->getName() ?>', $<?= $property->getName() ?>);
<?php else: ?>
        $this-><?= $property->getName() ?> = $<?= $property->getName() ?>;
<?php endif; ?>
    }
<?php endforeach; ?>
<?php endif; ?>
<?php if ($command->generateRelations && !empty($relations)): ?>

    public function relationQuery(string $name): ActiveQueryInterface
    {
        return match ($name) {
<?php foreach ($relations as $relation): ?>
            '<?= $relation->getName() ?>' => $this-><?= $relation->getQueryMethodName() ?>(),
<?php endforeach; ?>
            default => parent::relationQuery($name),
        };
    }
<?php foreach ($relations as $relation): ?>

    public function <?= $relation->getGetterMethodName() ?>(): <?= $relation->getGetterReturnType() . PHP_EOL ?>
    {
        return $this->relation('<?= $relation->getName() ?>');
    }

    public function <?= $relation->getQueryMethodName() ?>(): ActiveQueryInterface
    {
        return $this-><?= $relation->isHasOne() ? 'hasOne' : 'hasMany' ?>(<?= $relation->getRelatedModel() ?>::class, <?= var_export($relation->getLink(), true) ?>)<?= "->inverseOf('" . $relation->getInverseOf() . "')" ?>;
    }
<?php endforeach; ?>
<?php endif; ?>
}
