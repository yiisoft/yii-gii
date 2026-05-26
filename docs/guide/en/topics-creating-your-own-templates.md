Creating Your Own Templates
===========================

Each generator has a default template directory in its generator class directory:

* `src/Generator/Controller/default`
* `src/Generator/ActiveRecord/default`

To customize generated code, copy the default template directory into your application and edit the copied files.
For example:

```text
templates/gii/controller/controller.php
templates/gii/controller/view.php
```

Register the template in application parameters. Template keys are grouped by generator ID:

```php
'yiisoft/yii-gii' => [
    'parameters' => [
        'templates' => [
            'controller' => [
                'custom' => '@root/templates/gii/controller',
            ],
        ],
    ],
],
```

Use the template from the console with `--template`:

```shell
./yii gii:controller SampleController --template=custom --viewsPath=@runtime/gii-views
```

The template directory must contain all files required by the generator. The controller generator requires
`controller.php` and `view.php`; the Active Record generator requires `model.php`.
