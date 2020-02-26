<?php
use yii\helpers\Html;
use yii\helpers\Yii;

/* @var $this \yii\web\View */
/* @var $generators \Yiisoft\Yii\Gii\Generator[] */
/* @var $activeGenerator \Yiisoft\Yii\Gii\Generator */
/* @var $content string */

$generators = Yii::getApp()->controller->module->generators;
$activeGenerator = Yii::getApp()->controller->generator;
?>
<?php $this->beginContent('@Yiisoft/Yii/Gii/views/layouts/main.php'); ?>
<div class="row">
    <div class="col-md-3 col-sm-4">
        <div class="list-group">
            <?php
            $classes = ['list-group-item', 'd-flex', 'justify-content-between', 'align-items-center'];
            foreach ($generators as $id => $generator) {
                $label = Html::tag('span', Html::encode($generator->getName())) . '<span class="icon"></span>';
                echo Html::a($label, ['default/view', 'id' => $id], [
                    'class' => $generator === $activeGenerator ? array_merge($classes, ['active']) : $classes,
                ]);
            }
            ?>
        </div>
    </div>
    <div class="col-md-9 col-sm-8">
        <?= $content ?>
    </div>
</div>
<?php $this->endContent(); ?>
