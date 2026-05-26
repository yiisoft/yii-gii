<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://yiisoft.github.io/docs/images/yii_logo.svg" height="100px" alt="Yii">
    </a>
    <h1 align="center">Gii: Yii3 code generator</h1>
    <br>
</p>

[![Latest Stable Version](https://poser.pugx.org/yiisoft/yii-gii/v)](https://packagist.org/packages/yiisoft/yii-gii)
[![Total Downloads](https://poser.pugx.org/yiisoft/yii-gii/downloads)](https://packagist.org/packages/yiisoft/yii-gii)
[![Build status](https://github.com/yiisoft/yii-gii/actions/workflows/build.yml/badge.svg)](https://github.com/yiisoft/yii-gii/actions/workflows/build.yml)
[![Code Coverage](https://codecov.io/gh/yiisoft/yii-gii/branch/master/graph/badge.svg?token=JWRONWSQ5P)](https://codecov.io/gh/yiisoft/yii-gii)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Fyii-gii%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/yii-gii/master)
[![Static analysis](https://github.com/yiisoft/yii-gii/actions/workflows/static.yml/badge.svg?branch=master)](https://github.com/yiisoft/yii-gii/actions/workflows/static.yml?query=branch%3Amaster)
[![type-coverage](https://shepherd.dev/github/yiisoft/yii-gii/coverage.svg)](https://shepherd.dev/github/yiisoft/yii-gii)

This package provides code generation tools, called Gii, for [Yii3](https://www.yiiframework.com)
applications.

Gii includes JSON API endpoints and console commands for generating application code.

## Requirements

- PHP 8.1 - 8.5.

## Installation

The package could be installed with [Composer](https://getcomposer.org):

```shell
composer require --dev yiisoft/yii-gii
```

In an application using `yiisoft/config`, Gii configuration is added automatically by the Composer config plugin.

The Active Record generator is optional. To use it, install
[`yiisoft/active-record`](https://github.com/yiisoft/active-record), a concrete [Yii DB](https://github.com/yiisoft/db)
driver, and configure `Yiisoft\Db\Connection\ConnectionInterface`. For SQLite:

```shell
composer require yiisoft/active-record yiisoft/db-sqlite
```

Use the driver package that matches your database, such as `yiisoft/db-mysql`, `yiisoft/db-pgsql`, or
`yiisoft/db-sqlite`, and make sure the matching PDO extension is installed.

You need to define `Yiisoft\Db\Connection\ConnectionInterface` in your application if it is not already defined. See the
[Yii DB SQLite connection guide](https://github.com/yiisoft/db/blob/master/docs/guide/en/connection/sqlite.md) for a
SQLite configuration example.

## General usage

Gii registers JSON API routes under `/gii/api` when `yiisoft/yii-gii.enabled` is `true`.

```text
GET  /gii/api/generator
GET  /gii/api/generator/{generator}
POST /gii/api/generator/{generator}/preview
POST /gii/api/generator/{generator}/generate
POST /gii/api/generator/{generator}/diff
```

By default, only localhost is allowed:

```php
'yiisoft/yii-gii' => [
    'allowedIPs' => ['127.0.0.1', '::1'],
],
```

Console commands are registered automatically:

```shell
./yii help gii:controller
./yii gii:controller SampleController --actions=index --viewsPath=@runtime/gii-views --no-interaction

./yii help gii:active-record
./yii gii:active-record city --namespace=App\\Model --no-interaction
```

The Active Record command requires the table to exist in the configured database.

## Documentation

- Guide: [English](docs/guide/en/README.md)
- [Internals](docs/internals.md)

If you need help or have a question, the [Yii Forum](https://forum.yiiframework.com/c/yii-3-0/63) is a good place for that.
You may also check out other [Yii Community Resources](https://www.yiiframework.com/community).

## License

Gii: Yii3 code generator is free software. It's released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.

Maintained by [Yii Software](https://www.yiiframework.com/).

### Support the project

[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/yiisoft)

### Follow updates

[![Official website](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)
[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/yiiframework)
[![Telegram](https://img.shields.io/badge/telegram-join-1DA1F2?style=flat&logo=telegram)](https://t.me/yii3en)
[![Facebook](https://img.shields.io/badge/facebook-join-1DA1F2?style=flat&logo=facebook&logoColor=ffffff)](https://www.facebook.com/groups/yiitalk)
[![Slack](https://img.shields.io/badge/slack-join-1DA1F2?style=flat&logo=slack)](https://yiiframework.com/go/slack)
