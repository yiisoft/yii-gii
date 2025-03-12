<?php

declare(strict_types=1);

use Yiisoft\Strings\StringHelper;

/**
 * @var Yiisoft\Yii\Gii\Generator\ActiveRecord\Command $command
 * @var array<string, string> $properties
 */

echo "<?php\n";
?>

declare(strict_types=1);

namespace <?= $command->getNamespace() ?>;

use <?= $command->getBaseClass() ?>;

final class <?= $command->getModelName(); ?> extends <?= StringHelper::baseName($command->getBaseClass()) . PHP_EOL ?>
{
<?php
    /**
     * @psalm-var array $property
     */
    foreach ($properties as $property): ?>
    private <?=sprintf(
        '%s%s $%s',
        $property['isAllowNull'] ? '?' : '',
        (string)$property['type'],
        (string)$property['name'],
    )?>;
<?php endforeach; ?>
<?php if (!empty($properties)) {
    echo PHP_EOL;
} ?>
    public function tableName(): string
    {
        return '<?= $command->getTableName() ?>';
    }
}
