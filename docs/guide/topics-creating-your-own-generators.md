Creating your own generators
============================

Open the folder of any generator and you will see two files `form.php` and `Generator.php`.
One is the form, the second is the generator class. In order to create your own generator, you need to create or
override these classes in any folder. Again as in the previous section customize the configuration:

```php
//config/web.php for basic app
//..
if (YII_ENV_DEV) {    
    $config['modules']['gii'] = [
        'class' => Yiisoft\Yii\Gii\Gii::class,
        'allowedIPs' => ['127.0.0.1', '::1', '192.168.0.*', '192.168.178.20'],  
         'generators' => [
            'myCrud' => [
                'class' => app\myTemplates\crud\Generator::class,
                'templates' => [
                    'my' => '@app/myTemplates/crud/default',
                ]
            ]
        ],
    ];
}
```

```php
<?php
// @app/myTemplates/crud/Generator.php

namespace app\myTemplates\crud;

class Generator extends \Yiisoft\Yii\Gii\Generator
{
    public function getName()
    {
        return 'MY CRUD Generator';
    }

    public function getDescription()
    {
        return 'My crud generator. The same as a native, but he is mine...';
    }
    
    // ...
}
```

Open Gii Module and you will see a new generator appears in it.
