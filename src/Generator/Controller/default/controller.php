<?php declare(strict_types=1);
/**
 * This is the template for generating a controller class file.
 */

use Yiisoft\Strings\StringHelper;

/* @var $generator Yiisoft\Yii\Gii\Generator\Controller\Generator */

echo "<?php\n";
?>

namespace <?php echo $generator->getControllerNamespace() ?>;

class <?php echo StringHelper::baseName($generator->getControllerClass()) ?> <?php echo $generator->getBaseClass()
? 'extends \\' . trim(
    $generator->getBaseClass(),
    '\\'
) . "\n" : '' ?>
{
    public function getId(): string
    {
        return '<?php echo $generator->getControllerID() ?>';
    }

<?php foreach ($generator->getActionIDs() as $action) { ?>
    public function <?php echo $action ?>()
    {
        return $this->render('<?php echo $action ?>');
    }

<?php } ?>
}
