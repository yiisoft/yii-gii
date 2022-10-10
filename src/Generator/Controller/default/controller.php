<?php
declare(strict_types=1);
/**
 * This is the template for generating a controller class file.
 */

use Yiisoft\Strings\StringHelper;

/* @var $this Yiisoft\Yii\Gii\Generator\Controller\Generator */

$classDefinitionParts = [];
$classDefinitionParts[] = StringHelper::baseName($this->getControllerClass());
if ($this->getBaseClass() !== null) {
    $classDefinitionParts[] = 'extends \\' . trim($this->getBaseClass(), '\\');
}
$classDefinition = implode(' ', $classDefinitionParts) . PHP_EOL;


echo "<?php\n";
?>

declare(strict_types=1);

namespace <?= $this->getControllerNamespace() ?>;

use Yiisoft\Yii\View\ViewRenderer;

final class <?= $classDefinition; ?>
{
    public function __construct(private ViewRenderer $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer->withController($this);
    }
<?php foreach ($this->getActionIDs() as $action) : ?>

    public function <?= $action ?>()
    {
        return $this->viewRenderer->render('<?= $action ?>');
    }
<?php endforeach; ?>
}
