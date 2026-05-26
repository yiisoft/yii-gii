Installation
============

Install Gii as a development dependency:

```shell
composer require --dev yiisoft/yii-gii
```

Applications using `yiisoft/config`, including `yiisoft/app`, receive Gii routes, console commands, parameters, and DI
definitions automatically through the Composer config plugin.

The Active Record generator is optional. To use it, the application must have `yiisoft/active-record`, a Yii DB driver
package, and a `Yiisoft\Db\Connection\ConnectionInterface` definition. In a new `yiisoft/app` project, install the
driver matching your database, for example SQLite:

```shell
composer require yiisoft/active-record yiisoft/db-sqlite
```

Other driver packages include `yiisoft/db-mysql` and `yiisoft/db-pgsql`. Make sure the matching PDO extension is
installed, for example `pdo_sqlite` for SQLite.

If your application does not define a database connection yet, add one. See the
[Yii DB SQLite connection guide](https://github.com/yiisoft/db/blob/master/docs/guide/en/connection/sqlite.md) for a
SQLite configuration example.

Gii is enabled by default and allows local requests only:

```php
'yiisoft/yii-gii' => [
    'enabled' => true,
    'allowedIPs' => ['127.0.0.1', '::1'],
],
```

Disable it in production or in any environment where code generation should not be exposed:

```php
'yiisoft/yii-gii' => [
    'enabled' => false,
],
```
