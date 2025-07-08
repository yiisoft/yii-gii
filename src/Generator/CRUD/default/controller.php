<?php
declare(strict_types=1);
/**
 * This is the template for generating a controller class file.
 */

use Yiisoft\ActiveRecord\ActiveRecordFactory;
use Yiisoft\Strings\StringHelper;

/* @var $command Yiisoft\Yii\Gii\Generator\CRUD\Command */

$classDefinitionParts = [];
$classDefinitionParts[] = StringHelper::baseName($command->getModel()) . 'Controller';
if (!empty($command->getBaseClass())) {
    //$classDefinitionParts[] = 'extends \\' . trim($command->getBaseClass(), '\\');
}
$classDefinition = implode(' ', $classDefinitionParts) . PHP_EOL;

/**
 * @var $arF ActiveRecordFactory
 */

echo "<?php\n";
?>

declare(strict_types=1);

namespace <?= $command->getControllerNamespace() ?>;

use Yiisoft\ActiveRecord\ActiveRecordFactory;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Data\Reader\Iterable\IterableDataReader;
use Yiisoft\Yii\View\ViewRenderer;

final class <?= $classDefinition; ?>
{
    public function __construct(private ViewRenderer $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer->withController($this);
    }
<?php
foreach ($command->getActions() as $action) {
    echo PHP_EOL;
    switch ($action) {
        case 'index':
            echo <<<PHP
    public function index(ActiveRecordFactory \$activeRecordFactory)
    {
        \$query = \$activeRecordFactory->createQueryTo(\\{$command->getModel()}::class);
        \$paginator = (new OffsetPaginator(new IterableDataReader(\$query->all())))
            ->withPageSize(10);
        return \$this->viewRenderer->render('index', [
            'paginator' => \$paginator,
        ]);
    }
PHP;
            break;
        case 'view':
            echo <<<PHP
    public function view(ActiveRecordFactory \$activeRecordFactory, string \$id)
    {
        \$model = \$activeRecordFactory->createQueryTo(\\{$command->getModel()}::class)->findOne(\$id);
        return \$this->viewRenderer->render('view', [
            'model' => \$model,
        ]);
    }
PHP;
            break;
    }

}
echo PHP_EOL;
?>
}
