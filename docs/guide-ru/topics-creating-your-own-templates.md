�������� ����������� ��������
===========================

� ������ ���������� ���� ���������� ������ `Code Template`, ������� ��������� ������� ������ ��� ������������� ����.
��-���������, � Gii ���� ������ ���� ������ - `default`, �� �� ������ ������� �����������, ���������� ����� ��������.

���� �� �������� ����� `@app\vendor\yiisoft\yii2-gii\generators`, �� ���������� ��� ����� ����� �����������.

```
+ controller
- crud
    + default
+ extension
+ form
+ model
+ module
```

�� ����� - �������� �����������. ���� �� �������� ����� �� ���� �����, �� ���������� ��� ����� `default`, ��� ������� �������� ������ �������.

���������� ����� `@app\vendor\yiisoft\yii2-gii\generators\crud\default` � ������ �����, ��������, `@app\myTemplates\crud\`.
������ �������� ��� ����� � �������� ������ � ������������ �� ������ ��������, ��������, �������� `errorSummary` � ������������� `views\_form.php`:

```php
<?php
//...
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-form">

    <?= "<?php " ?>$form = ActiveForm::begin(); ?>
    <?= "<?=" ?> $form->errorSummary($model) ?> <!-- �������� ���� -->
//...
```

������ ����� �������� Gii � ����� �������. ��������� ��� ������ � ���������������� �����:

```php
// config/web.php ��� basic-����������
// ...
if (YII_ENV_DEV) {
    $config['modules']['gii'] = [
        '__class' => \Yiisoft\Yii\Gii\Gii::class,
        'allowedIPs' => ['127.0.0.1', '::1', '192.168.0.*', '192.168.178.20'],
        'generators' => [ // �����
            'crud' => [ // �������� ����������
                '__class' => \Yiisoft\Yii\Gii\Generators\Crud\Generator::class, // ����� ����������
                'templates' => [ // ��������� ��������� ��������
                    'myCrud' => '@app/myTemplates/crud/default', // ���_������� => ����_�_�������
                ]
            ]
        ],
    ];
}
```

�������� CRUD � �� �������, ��� � ���������� ������ `Code Template` �������� ��� ����������� ������.
