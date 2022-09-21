<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\DataResponse\Middleware\FormatDataResponseAsJson;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;
use Yiisoft\Validator\ValidatorInterface;
use Yiisoft\Yii\Debug\Api\Middleware\ResponseDataWrapper;
use Yiisoft\Yii\Gii\Controller\DefaultController;
use Yiisoft\Yii\Middleware\IpFilter;

/**
 * @var array $params
 */

return [
    Group::create('/gii/api')
        ->middleware(
            static function (ResponseFactoryInterface $responseFactory, ValidatorInterface $validator) use ($params) {
                return new IpFilter(
                    validator: $validator,
                    responseFactory: $responseFactory,
                    ipRanges: $params['yiisoft/yii-debug-api']['allowedIPs']
                );
            }
        )
        ->middleware(FormatDataResponseAsJson::class)
        ->middleware(ResponseDataWrapper::class)
        ->namePrefix('gii/api/')
        ->routes(
            Route::get('[/]')
                ->action([DefaultController::class, 'index'])
                ->name('index'),
            Route::get('/generator/{id}/view')
                ->action([DefaultController::class, 'view'])
                ->name('view'),
            Route::get('/generator/{id}/preview/{file}')
                ->action([DefaultController::class, 'preview'])
                ->name('preview'),
            Route::get('/generator/{id}/diff/{file}')
                ->action([DefaultController::class, 'diff'])
                ->name('diff')
        ),
];
