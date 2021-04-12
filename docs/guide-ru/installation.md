Установка
============

## Установка composer-пакета

Предпочтительнее установить это расширение через [composer](http://getcomposer.org/download/).

Либо запустите

```
php composer.phar require --dev --prefer-dist yiisoft/yii-gii
```

либо добавьте

```
"yiisoft/yii-gii": "^3.0@dev"
```

в require-dev секцию Вашего файла `composer.json`.

Теперь Gii доступен по адресу:

```
http://localhost/path/to/index.php?r=gii
```

Если Вы используете "красивые" адреса (pretty URLs), то используйте такой URL:

```
http://localhost/path/to/index.php/gii
```

> Note: По-умолчанию, если Вы запускаете gii с ip-адреса, отличного от localhost, доступ к нему будет закрыт.
> Чтобы изменить это поведение, добавьте ip-адреса, которым разрешен доступ, в конфигурацию:
>
```php
'gii' => [
    'allowedIPs' => ['127.0.0.1', '::1', '192.168.0.*', '192.168.178.20'] // настройте, как Вам нужно здесь
    // ...
],
```
Если Вы настроили Gii аналогичным образом в консольном приложении, Вы сможете таким образом через консоль запустить Gii:

```
# измените путь на базовый Вашего приложения
cd path/to/AppBasePath
# эта команда покажет справку Gii
yii help gii
# эта команда покажет справку по генератору Моделей в Gii
yii help gii/model
# сгенерирует модель City из таблицы city
yii gii/model --tableName=city --modelClass=City
```

### Basic-приложение

В шаблоне Basic-приложения структура конфигурации несколько отличается, поэтому Gii должен быть
настроен в `config/web.php`:

```php
// ...
if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    // настройка конфигурации для разработки
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = \yii\debug\Module::class;
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = \Yiisoft\Yii\Gii\Gii::class; // <--- здесь
}
```
А для настройки ip-адресов надо сделать следующее:
```php
if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    // настройка конфигурации для разработки
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = \yii\debug\Module::class;
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => \Yiisoft\Yii\Gii\Gii::class,
        'allowedIPs' => ['127.0.0.1', '::1', '192.168.0.*', '192.168.178.20'],
    ];
}
```
