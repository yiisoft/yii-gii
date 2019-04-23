<?php

use Yiisoft\Helpers\InflectorHelper;
use Yiisoft\Strings\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();

echo "<?php\n";
?>

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */

$this->title = <?= strtr($generator->generateString('Update ' .
    InflectorHelper::camel2words(StringHelper::basename($generator->modelClass)) .
    ': {nameAttribute}', ['nameAttribute' => '{nameAttribute}']), [
    '{nameAttribute}\'' => '\' . $model->' . $generator->getNameAttribute()
]) ?>;
$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString(InflectorHelper::pluralize(InflectorHelper::camel2words(StringHelper::basename($generator->modelClass)))) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model-><?= $generator->getNameAttribute() ?>, 'url' => ['view', <?= $urlParams ?>]];
$this->params['breadcrumbs'][] = <?= $generator->generateString('Update') ?>;
?>
<div class="<?= InflectorHelper::camel2id(StringHelper::basename($generator->modelClass)) ?>-update">

    <h1><?= '<?= ' ?>Html::encode($this->title) ?></h1>

    <?= '<?= ' ?>$this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
