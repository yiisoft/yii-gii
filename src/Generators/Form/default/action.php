<?php
/**
 * This is the template for generating an action view file.
 */

use Yiisoft\Inflector\InflectorHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\form\Generator */

echo "<?php\n";
?>

public function action<?= InflectorHelper::id2camel(trim(basename($generator->viewName), '_')) ?>()
{
    $model = new \<?= ltrim($generator->modelClass, '\\') ?><?= empty($generator->scenarioName) ? "()" : "(['scenario' => '{$generator->scenarioName}'])" ?>;

    if ($model->load(Yii::getApp()->request->post())) {
        if ($model->validate()) {
            // form inputs are valid, do something here
            return;
        }
    }

    return $this->render('<?= basename($generator->viewName) ?>', [
        'model' => $model,
    ]);
}
