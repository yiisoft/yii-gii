<?php
/**
 * This is the template for generating a controller class file.
 */

use Yiisoft\Strings\Inflector;
use Yiisoft\Strings\StringHelper;

/* @var $generator Yiisoft\Yii\Gii\Generators\Controller\Generator */

echo "<?php\n";
?>

namespace <?= $generator->getControllerNamespace() ?>;

class <?= StringHelper::basename($generator->controllerClass) ?> <?= $generator->baseClass ? 'extends \\'.trim(
        $generator->baseClass,
        '\\'
    )."\n" : '' ?>
{
    public function getId(): string
    {
        return '<?= $generator->getControllerID() ?>';
    }

<?php foreach ($generator->getActionIDs() as $action): ?>
    public function <?= $action ?>()
    {
        return $this->render('<?= $action ?>');
    }

<?php endforeach; ?>
}
