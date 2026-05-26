Basic Usage
===========

Gii registers JSON API routes under `/gii/api`:

```text
GET  /gii/api/generator
GET  /gii/api/generator/{generator}
POST /gii/api/generator/{generator}/preview
POST /gii/api/generator/{generator}/generate
POST /gii/api/generator/{generator}/diff
```

The built-in generator IDs are:

* `controller` - generates a controller class and action view files.
* `active-record` - generates an Active Record class for an existing database table.

The same generators are available from the console:

```shell
./yii help gii:controller
./yii help gii:active-record
```

Generate a controller:

```shell
./yii gii:controller SampleController --actions=index --viewsPath=@runtime/gii-views --no-interaction
```

`yiisoft/app` does not define the `@views` alias by default, so either pass `--viewsPath` or define a `@views` alias in
your application.

Generate an Active Record class for an existing table:

```shell
./yii gii:active-record city --namespace=App\\Model --no-interaction
```

The Active Record generator needs a working database connection and the target table must already exist.
