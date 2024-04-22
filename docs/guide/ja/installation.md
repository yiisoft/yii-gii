インストール
============

## Composer パッケージを取得する

このエクステンションをインストールするのに推奨される方法は [composer](https://getcomposer.org/download/) によるものです。

下記のコマンドを実行してください。

```
php composer.phar require --dev --prefer-dist yiisoft/yii-gii
```

または、あなたの `composer.json` ファイルの `require` セクションに、下記を追加してください。

```
"yiisoft/yii-gii": "^3.0@dev"
```

そうすると、次の URL で Gii にアクセスすることが出来ます。

```
http://localhost/path/to/index.php?r=gii
```

綺麗な URL を有効にしている場合は、次の URL を使います。

```
http://localhost/path/to/index.php/gii
```

> Note: ローカル・ホスト以外の IP から Gii にアクセスしようとすると、デフォルトでは、アクセスが拒否されます。
> このデフォルトを回避するためには、許可される IP アドレスを構成情報に追加してください。
>
```php
'gii' => [
    'allowedIPs' => ['127.0.0.1', '::1', '192.168.0.*', '192.168.178.20'] // 必要に応じて修正
    // ...
],
```

コンソールアプリケーションの構成情報において同じように Gii を構成すると、次のようにして、コマンド・ウィンドウから Gii にアクセスすることが出来ます。

```
# パスをアプリケーションのベース・パスに変更
cd path/to/AppBasePath

# Gii に関するヘルプ情報を表示
yii help gii

# Gii のモデル・ジェネレータに関するヘルプ情報を表示
yii help gii/model

# city テーブルから City モデルを生成
yii gii/model --tableName=city --modelClass=City
```
