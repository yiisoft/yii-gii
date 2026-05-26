Installation
============

Install Gii as a development dependency if your application already has a Yii DB driver:

```shell
composer require --dev yiisoft/yii-gii
```

Gii uses Yii DB for Active Record generation, so the application must have a database driver package and a
`Yiisoft\Db\Connection\ConnectionInterface` definition. In a new `yiisoft/app` project, install Gii together with the
driver matching your database:

```shell
composer require --dev yiisoft/yii-gii yiisoft/db-sqlite
```

Other driver packages include `yiisoft/db-mysql` and `yiisoft/db-pgsql`. Make sure the matching PDO extension is
installed, for example `pdo_sqlite` for SQLite.

Applications using `yiisoft/config`, including `yiisoft/app`, receive Gii routes, console commands, parameters, and DI
definitions automatically through the Composer config plugin.

If your application does not define a database connection yet, add one. A minimal SQLite example for `yiisoft/app` is:

```php
<?php

declare(strict_types=1);

use Yiisoft\Cache\ArrayCache;
use Yiisoft\Db\Cache\SchemaCache;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Sqlite\Connection;
use Yiisoft\Db\Sqlite\Driver;

return [
    ConnectionInterface::class => [
        'class' => Connection::class,
        '__construct()' => [
            'driver' => new Driver('sqlite:' . dirname(__DIR__, 2) . '/runtime/app.db'),
            'schemaCache' => new SchemaCache(new ArrayCache()),
        ],
    ],
];
```

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
