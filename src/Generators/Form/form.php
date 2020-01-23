<?php

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator Yiisoft\Yii\Gii\Generators\Form\Generator */

echo $form->field($generator, 'viewName');
echo $form->field($generator, 'modelClass');
echo $form->field($generator, 'scenarioName');
echo $form->field($generator, 'viewPath');
echo $form->field($generator, 'enableI18N')->checkbox();
echo $form->field($generator, 'messageCategory');
