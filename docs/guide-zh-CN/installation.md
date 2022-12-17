安装
============

## 获取 Composer 包

安装此扩展的首选方法是通过 [composer](https://getcomposer.org/download/).

执行

```
php composer.phar require --dev --prefer-dist yiisoft/yii-gii
```

或者在项目的 `composer.json` 中的 require-dev 部分添加如下代码

```
"yiisoft/yii-gii": "^3.0@dev"
```

然后，可以通过以下 URL 访问 Gii ：

```
http://localhost/path/to/index.php?r=gii
```

如果开启了 pretty URLs, 则这样访问:

```
http://localhost/path/to/index.php/gii
```

> 注意：如果从除 localhost 之外的 IP 地址访问 gii ，访问将被默认拒绝。
> 要规避该默认值，则需将允许的 IP 地址添加到配置中：
>
```php
'gii' => [
    'allowedIPs' => ['127.0.0.1', '::1', '192.168.0.*', '192.168.178.20'] // adjust this to your needs
    // ...
],
```

如果在控制台应用程序配置中对 Gii 做了类似的配置，那么还可以通过命令窗口访问Gii，如下所示：

```
# 切换至项目根路径
cd path/to/AppBasePath

# 查看 Gii 帮助信息
yii help gii

# 查看 Gii 中关于 model 生成器的帮助信息
yii help gii/model

# 基于 city 数据表生成 City model
yii gii/model --tableName=city --modelClass=City
```
