<?php

declare(strict_types=1);

use Yiisoft\Strings\StringHelper;
use Yiisoft\Yii\Gii\Generator\ActiveRecord\Column;

/**
 * @var Yiisoft\Yii\Gii\Generator\ActiveRecord\Command $command
 * @var list<Column> $properties
 */

echo "<?php\n";
?>

declare(strict_types=1);

namespace <?= $command->getNamespace() ?>;

use <?= $command->getBaseClass() ?>;

final class <?= $command->getModelName(); ?> extends <?= StringHelper::baseName($command->getBaseClass()) . PHP_EOL ?>
{
<?php foreach ($properties as $property): ?>
    private <?=sprintf(
        '%s%s $%s',
        $property->isAllowNull ? '?' : '',
        $property->type,
        $property->name,
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
