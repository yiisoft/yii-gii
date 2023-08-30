<?php
declare(strict_types=1);
/**
 * This is the template for generating a controller class file.
 */

/* @var $command Yiisoft\Yii\Gii\Generator\ActiveRecord\Command */

use Yiisoft\Strings\StringHelper;

echo "<?php\n";
?>

declare(strict_types=1);

namespace <?= $command->getNamespace() ?>;

use <?= $command->getBaseClass() ?>;

final class <?= $command->getModelName(); ?> extends <?= StringHelper::baseName($command->getBaseClass()) . PHP_EOL ?>
{
    public function tableName(): string
    {
        return '<?= $command->getTableName() ?>';
    }
}
