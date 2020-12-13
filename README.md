<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Gii Extension for Yii</h1>
    <br>
</p>

This extension provides a Web-based code generator, called Gii, for [Yii framework](http://www.yiiframework.com) applications.
You can use Gii to quickly generate models, forms, modules, CRUD, etc.

For license information check the [LICENSE](LICENSE.md)-file.

Documentation is at [docs/guide/README.md](docs/guide/README.md).

[![Latest Stable Version](https://poser.pugx.org/yiisoft/yii-gii/v/stable.png)](https://packagist.org/packages/yiisoft/yii-gii)
[![Total Downloads](https://poser.pugx.org/yiisoft/yii-gii/downloads.png)](https://packagist.org/packages/yiisoft/yii-gii)
[![Build Status](https://github.com/yiisoft/yii-gii/workflows/build/badge.svg)](https://github.com/yiisoft/yii-gii/actions?query=workflow%3Abuild)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/yii-gii/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/yii-gii/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/yii-gii/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/yii-gii/?branch=master)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Fyii-gii%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/yii-gii/master)
[![static analysis](https://github.com/yiisoft/yii-gii/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/yii-gii/actions?query=workflow%3A%22static+analysis%22)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

```
composer require --prefer-dist yiisoft/yii-gii
```

Usage
-----

Once the extension is installed, simply modify your application configuration as follows:

```php
return [
    'bootstrap' => ['gii'],
    'modules' => [
        'gii' => [
            '__class' => Yiisoft\Yii\Gii\Gii::class,
        ],
        // ...
    ],
    // ...
];
```

You can then access Gii through the following URL:

```
http://localhost/path/to/index.php?r=gii
```

or if you have enabled pretty URLs, you may use the following URL:

```
http://localhost/path/to/index.php/gii
```

Using the same configuration for your console application, you will also be able to access Gii via
command line as follows,

```
# change path to your application's base path
cd path/to/AppBasePath

# show help information about Gii
yii help gii

# show help information about the model generator in Gii
yii help gii/model

# generate City model from city table
yii gii/model --tableName=city --modelClass=City
```
