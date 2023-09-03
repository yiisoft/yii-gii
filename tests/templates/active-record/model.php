<?php
declare(strict_types=1);
/**
 * This is the template for generating a controller class file.
 */

/* @var $command Yiisoft\Yii\Gii\Generator\ActiveRecord\Command */

echo "<?php\n";
?>

declare(strict_types=1);

namespace <?= $command->getNamespace() ?>;

use <?= $command->getBaseClass() ?>;

final custom class <?= $command->getModelName(); ?>
