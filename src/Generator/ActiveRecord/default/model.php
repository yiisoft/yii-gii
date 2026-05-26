<?php

declare(strict_types=1);

use Yiisoft\Strings\StringHelper;
use Yiisoft\VarDumper\VarDumper;
use Yiisoft\Yii\Gii\Generator\ActiveRecord\AbstractRelation;
use Yiisoft\Yii\Gii\Generator\ActiveRecord\Property;
use Yiisoft\Yii\Gii\Generator\ActiveRecord\Command;

/**
 * @var Command $command
 * @var array<string, Property> $properties
 * @var array<string, AbstractRelation> $relations
 */

$needsConstructor = false;
$useExpression = false;

foreach ($properties as $property) {
    if (!$needsConstructor && $property->isDefaultValueNotConstant()) {
        $needsConstructor = true;
    }

    if (!$useExpression && $property->isDefaultValueExpression()) {
        $useExpression = true;
    }
}

echo "<?php\n";
?>

declare(strict_types=1);

namespace <?= $command->namespace ?>;

use <?= $command->parentClass ?>;
<?php if ($command->useRepositoryTrait): ?>
use Yiisoft\ActiveRecord\Trait\RepositoryTrait;
<?php endif; ?>
<?php if ($command->usePrivatePropertiesTrait()): ?>
use Yiisoft\ActiveRecord\Trait\PrivatePropertiesTrait;
<?php endif; ?>
<?php if ($command->generateRelations && !empty($relations)): ?>
use Yiisoft\ActiveRecord\ActiveQueryInterface;
<?php endif; ?>
<?php if ($useExpression): ?>
use Yiisoft\Db\Expression\Expression;
<?php endif; ?>

final class <?= $command->getModelName(); ?> extends <?= StringHelper::baseName($command->parentClass) . \PHP_EOL ?>
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
    <?= \sprintf(
        '%s %s $%s%s',
        $command->propertyVisibility,
        $property->getType(),
        $property->getName(),
        $property->isDefaultValueConstant() ? ' = ' . $property->getDefaultValue() : '',
    ) ?>;
<?php endforeach; ?>
<?php if (!empty($properties)): ?>

<?php endif; ?>
<?php if ($needsConstructor): ?>
    public function __construct()
    {
<?php foreach ($properties as $property): ?>
<?php if ($property->isDefaultValueNotConstant()): ?>
        $this-><?= $property->getName() ?> = <?= $property->getDefaultValue() ?>;
<?php endif; ?>
<?php endforeach; ?>
    }

<?php endif; ?>
    public function tableName(): string
    {
        return '<?= $command->table ?>';
    }
<?php if ($command->generateGettersSetters): ?>
<?php foreach ($properties as $property): ?>

    public function get<?= $property->getPascalCaseName() ?>(): <?= $property->getReturnType() . \PHP_EOL ?>
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

    public function <?= $relation->getGetterMethodName() ?>(): <?= $relation->getGetterReturnType() . \PHP_EOL ?>
    {
        return $this->relation('<?= $relation->getName() ?>');
    }

    public function <?= $relation->getQueryMethodName() ?>(): ActiveQueryInterface
    {
        return $this-><?= $relation->isHasOne() ? 'hasOne' : 'hasMany' ?>(<?= $relation->getRelatedModel() ?>::class, <?= VarDumper::create($relation->getLink())->export(false) ?>)<?= "->inverseOf('" . $relation->getInverseOf() . "')" ?>;
    }
<?php endforeach; ?>
<?php endif; ?>
}
