<?php declare(strict_types=1);
/**
 * This is the template for generating a controller class file.
 */

use Yiisoft\Strings\StringHelper;

/* @var $generator Yiisoft\Yii\Gii\Generator\Controller\Generator */

echo "<?php\n";
?>

namespace <?= $generator->getControllerNamespace() ?>;

class <?= StringHelper::baseName($generator->getControllerClass()) ?> <?= $generator->getBaseClass()
? 'extends \\' . trim(
    $generator->getBaseClass(),
    '\\'
) . "\n" : '' ?>
{
    public function getId(): string
    {
        return '<?= $generator->getControllerID() ?>';
    }

<?php foreach ($generator->getActionIDs() as $action) { ?>
    public function <?= $action ?>()
    {
        return $this->render('<?= $action ?>');
    }

<?php } ?>
}
