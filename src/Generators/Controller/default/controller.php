<?php
/**
 * This is the template for generating a controller class file.
 */

use Yiisoft\Strings\Inflector;
use Yiisoft\Strings\StringHelper;

/* @var $this yii\web\View */
/* @var $generator Yiisoft\Yii\Gii\Generators\Controller\Generator */

echo "<?php\n";
?>

namespace <?= $generator->getControllerNamespace() ?>;

class <?= StringHelper::basename($generator->controllerClass) ?> extends <?= '\\' . trim($generator->baseClass, '\\') . "\n" ?>
{
<?php foreach ($generator->getActionIDs() as $action): ?>
    public function action<?= Inflector::id2camel($action) ?>()
    {
        return $this->render('<?= $action ?>');
    }

<?php endforeach; ?>
}
