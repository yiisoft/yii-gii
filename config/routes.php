<?php

declare(strict_types=1);

/**
 * @var array $params
 */

use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\DataResponse\Middleware\FormatDataResponseAsJson;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;
use Yiisoft\Validator\ValidatorInterface;
use Yiisoft\Yii\Gii\Controller\DefaultController;
use Yiisoft\Yii\Middleware\CorsAllowAll;
use Yiisoft\Yii\Middleware\IpFilter;

if (!(bool) ($params['yiisoft/yii-gii']['enabled'] ?? false)) {
    return [];
}

return [
    Group::create('/gii/api')
        ->withCors(CorsAllowAll::class)
        ->middleware(
            static function (ResponseFactoryInterface $responseFactory, ValidatorInterface $validator) use ($params) {
                return new IpFilter(
                    validator: $validator,
                    responseFactory: $responseFactory,
                    ipRanges: $params['yiisoft/yii-gii']['allowedIPs']
                );
            }
        )
        ->middleware(FormatDataResponseAsJson::class)
        ->namePrefix('gii/api/')
        ->routes(
            Group::create('/generator')
                ->routes(
                    Route::get('[/]')
                        ->action([DefaultController::class, 'list'])
                        ->name('list'),
                    Route::get('/{generator}')
                        ->action([DefaultController::class, 'get'])
                        ->name('generator'),
                    Route::post('/{generator}/preview')
                        ->action([DefaultController::class, 'preview'])
                        ->name('preview'),
                    Route::post('/{generator}/generate')
                        ->action([DefaultController::class, 'generate'])
                        ->name('generate'),
                    Route::post('/{generator}/diff')
                        ->action([DefaultController::class, 'diff'])
                        ->name('diff')
                )
        ),
];
