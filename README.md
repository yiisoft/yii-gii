<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://yiisoft.github.io/docs/images/yii_logo.svg" height="100px" alt="Yii">
    </a>
    <h1 align="center">Gii Extension for Yii</h1>
    <br>
</p>

[![Latest Stable Version](https://poser.pugx.org/yiisoft/yii-gii/v)](https://packagist.org/packages/yiisoft/yii-gii)
[![Total Downloads](https://poser.pugx.org/yiisoft/yii-gii/downloads)](https://packagist.org/packages/yiisoft/yii-gii)
[![Monthly Downloads](https://poser.pugx.org/yiisoft/yii-gii/d/monthly)](https://packagist.org/packages/rossaddison/yii-auth-client)
[![Daily Downloads](https://poser.pugx.org/yiisoft/yii-gii/d/daily)](https://packagist.org/packages/rossaddison/yii-auth-client)
[![Build status](https://github.com/yiisoft/yii-gii/actions/workflows/build.yml/badge.svg)](https://github.com/yiisoft/yii-gii/actions/workflows/build.yml)
[![Code coverage](https://codecov.io/gh/yiisoft/yii-gii/graph/badge.svg?token=JWRONWSQ5P)](https://codecov.io/gh/yiisoft/yii-gii)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Fyii-gii%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/yii-gii/master)
[![static analysis](https://github.com/yiisoft/yii-gii/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/yii-gii/actions?query=workflow%3A%22static+analysis%22)

This extension provides a Web-based code generator, called Gii, for [Yii framework](https://www.yiiframework.com) applications.
You can use Gii to quickly generate models, forms, modules, CRUD, etc.

## Requirements

- PHP 8.3 or higher.

## Installation

The package could be installed with [Composer](https://getcomposer.org):

```shell
composer require yiisoft/yii-gii
```

## General usage

Once the extension is installed, simply modify your application configuration as follows:

```php
return [
    'bootstrap' => ['gii'],
    'modules' => [
        'gii' => [
            'class' => Yiisoft\Yii\Gii\Gii::class,
        ],
        // ...
    ],
    // ...
];
```

You can then access Gii through the following URL:

```text
http://localhost/path/to/index.php?r=gii
```

or if you have enabled pretty URLs, you may use the following URL:

```text
http://localhost/path/to/index.php/gii
```

Using the same configuration for your console application, you will also be able to access Gii via
command line as follows,

```shell
# change path to your application's base path
cd path/to/AppBasePath

# show help information about Gii
yii help gii

# show help information about the model generator in Gii
yii help gii/model

# generate City model from city table
yii gii/model --tableName=city --modelClass=City
```

## Documentation

- Guide: [English](docs/guide/en/README.md), [Português - Brasil](docs/guide/pt-BR/README.md), [Русский](docs/guide/ru/README.md), [日本語](docs/guide/ja/README.md), [中国人](docs/guide/zh-CN/README.md)
- [Internals](docs/internals.md)

If you need help or have a question, the [Yii Forum](https://forum.yiiframework.com/c/yii-3-0/63) is a good place for that.
You may also check out other [Yii Community Resources](https://www.yiiframework.com/community).

## License

The Gii Extension for Yii is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.

Maintained by [Yii Software](https://www.yiiframework.com/).

## Support the project

[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/yiisoft)

## Follow updates

[![Official website](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)
[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/yiiframework)
[![Telegram](https://img.shields.io/badge/telegram-join-1DA1F2?style=flat&logo=telegram)](https://t.me/yii3en)
[![Facebook](https://img.shields.io/badge/facebook-join-1DA1F2?style=flat&logo=facebook&logoColor=ffffff)](https://www.facebook.com/groups/yiitalk)
[![Slack](https://img.shields.io/badge/slack-join-1DA1F2?style=flat&logo=slack)](https://yiiframework.com/go/slack)
