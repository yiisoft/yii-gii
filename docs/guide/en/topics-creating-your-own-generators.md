Creating Your Own Generators
============================

A generator consists of:

* a command data object implementing `Yiisoft\Yii\Gii\GeneratorCommandInterface`;
* a generator class implementing `Yiisoft\Yii\Gii\GeneratorInterface`;
* optional template files used by the generator.

The built-in generators in `src/Generator/Controller` and `src/Generator/ActiveRecord` are the best references for the
current extension API.

Register custom generators in application parameters:

```php
'yiisoft/yii-gii' => [
    'generators' => [
        [
            'class' => App\Gii\Report\Generator::class,
            'parameters' => [],
        ],
    ],
],
```

The generator ID is returned by `Generator::getId()`. After registration, it is available from the HTTP API:

```text
GET /gii/api/generator/report
```

If you also need a console command, create a Symfony console command for your generator and register it in
`yiisoft/yii-console.commands`.
