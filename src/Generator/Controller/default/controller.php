<?php
declare(strict_types=1);
/**
 * This is the template for generating a controller class file.
 */

use Yiisoft\Strings\StringHelper;

/* @var $command Yiisoft\Yii\Gii\Generator\Controller\Command */

$classDefinitionParts = [];
$classDefinitionParts[] = StringHelper::baseName($command->getControllerClass());
if (!empty($command->getBaseClass())) {
    $classDefinitionParts[] = 'extends \\' . trim($command->getBaseClass(), '\\');
}
$classDefinition = implode(' ', $classDefinitionParts) . PHP_EOL;


echo "<?php\n";
?>

declare(strict_types=1);

namespace <?= $command->getControllerNamespace() ?>;

use Yiisoft\Yii\View\ViewRenderer;

final class <?= $classDefinition; ?>
{
    public function __construct(private ViewRenderer $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer->withController($this);
    }
<?php foreach ($command->getActions() as $action) : ?>

    public function <?= $action ?>()
    {
        return $this->viewRenderer->render('<?= $action ?>');
    }
<?php endforeach; ?>
}
