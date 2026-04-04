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

namespace <?= $command->getNamespace() ?>;

use <?= $command->getBaseClass() ?>;
<?php if ($command->isUseRepositoryTrait()): ?>
use Yiisoft\ActiveRecord\Trait\RepositoryTrait;
<?php endif; ?>
<?php if ($command->isUsePrivatePropertiesTrait()): ?>
use Yiisoft\ActiveRecord\Trait\PrivatePropertiesTrait;
<?php endif; ?>
<?php if ($command->isGenerateRelations() && !empty($relations)): ?>
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
use <?= $command->getNamespace() . '\\' . $relatedModel ?>;
<?php endforeach; ?>

final class <?= $command->getModelName(); ?> extends <?= StringHelper::baseName($command->getBaseClass()) . PHP_EOL ?>
{
<?php if ($command->isUseRepositoryTrait()): ?>
    use RepositoryTrait;
<?php endif; ?>
<?php if ($command->isUsePrivatePropertiesTrait()): ?>
    use PrivatePropertiesTrait;
<?php endif; ?>
<?php if ($command->isUseRepositoryTrait() || $command->isUsePrivatePropertiesTrait()): ?>

<?php endif; ?>
<?php foreach ($properties as $property): ?>
    <?= $command->getPropertyVisibility() ?> <?=sprintf(
        '%s%s $%s%s',
        $property->isAllowNull ? '?' : '',
        $property->type,
        $property->name,
        $property->hasDefaultValue() ? ' = ' . $property->getPhpDefaultValue() : '',
    )?>;
<?php endforeach; ?>
<?php if (!empty($properties)): ?>

<?php endif; ?>
    public function tableName(): string
    {
        return '<?= $command->getTableName() ?>';
    }
<?php if ($command->isGenerateGettersSetters()): ?>
<?php foreach ($properties as $property): ?>

    public function get<?= ucfirst($property->name) ?>(): <?= ($property->isAllowNull ? '?' : '') . $property->type . PHP_EOL ?>
    {
<?php if ($property->shouldUseNullCoalescing() && $property->isAllowNull): ?>
        return $this-><?= $property->name ?> ?? null;
<?php else: ?>
        return $this-><?= $property->name ?>;
<?php endif; ?>
    }

    public function set<?= ucfirst($property->name) ?>(<?= ($property->isAllowNull ? '?' : '') . $property->type ?> $<?= $property->name ?>): void
    {
<?php if ($property->isPrimaryKey): ?>
        $this->set('<?= $property->name ?>', $<?= $property->name ?>);
<?php else: ?>
        $this-><?= $property->name ?> = $<?= $property->name ?>;
<?php endif; ?>
    }
<?php endforeach; ?>
<?php endif; ?>
<?php if ($command->isGenerateRelations() && !empty($relations)): ?>

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
        return $this-><?= $relation->isHasOne() ? 'hasOne' : 'hasMany' ?>(<?= $relation->relatedModel ?>::class, <?= var_export($relation->link, true) ?>)<?= $relation->inverseOf ? "->inverseOf('" . $relation->inverseOf . "')" : '' ?>;
    }
<?php endforeach; ?>
<?php endif; ?>
}
