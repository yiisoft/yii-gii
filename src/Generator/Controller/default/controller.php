<?php
declare(strict_types=1);
/**
 * This is the template for generating a controller class file.
 */

use Yiisoft\Strings\StringHelper;

/* @var $generator Yiisoft\Yii\Gii\Generator\Controller\Generator */

$classModifiers = [];
$classModifiers[] = StringHelper::baseName($generator->getControllerClass());
if ($generator->getBaseClass() !== null) {
    $classModifiers[] = 'extends \\' . trim($generator->getBaseClass(), '\\');
}
$classDefinition = implode(' ', $classModifiers) . PHP_EOL;


echo "<?php\n";
?>

declare(strict_types=1);

namespace <?= $generator->getControllerNamespace() ?>;

use Yiisoft\Yii\View\ViewRenderer;

final class <?= $classDefinition; ?>
{
    public function __construct(private ViewRenderer $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer->withController($this);
    }
<?php foreach ($generator->getActionIDs() as $action) : ?>

    public function <?= $action ?>()
    {
        return $this->viewRenderer->render('<?= $action ?>');
    }
<?php endforeach; ?>
}
