�������� ����������� �����������
============================

�������� ����� ������ ���������� � �� ������� ��� ����� `form.php` � `Generator.php`.
������ - ��� �����, ������ - ����� ����������. ��� ����, ����� ������� ���� ??���������,
��� ���������� ������� ��� ���������� ��� ������ � �����-������ ������ �����. �����, ���
������� � ���������� �������, ������� ��������� � ������������:

```php
//config/web.php ��� basic-����������
//..
if (YII_ENV_DEV) {
    $config['modules']['gii'] = [
        '__class' => \Yiisoft\Yii\Gii\Gii::class,
        'allowedIPs' => ['127.0.0.1', '::1', '192.168.0.*', '192.168.178.20'],
         'generators' => [
            'myCrud' => [
                '__class' => \app\myTemplates\crud\Generator::class,
                'templates' => [
                    'my' => '@app/myTemplates/crud/default',
                ]
            ]
        ],
    ];
}
```

```php
// @app/myTemplates/crud/Generator.php
<?php
namespace app\myTemplates\crud;

class Generator extends \Yiisoft\Yii\Gii\Generator
{
    public function getName()
    {
        return 'MY CRUD Generator';
    }

    public function getDescription()
    {
        return '��� crud-���������. ����� �� ��� � ���������, �� ���� ���...';
    }

    // ...
}
```

�������� Gii Module � ���������, ��� � ��� �������� ����� ���������.
