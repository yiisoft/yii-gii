<?php

declare(strict_types=1);

/**
 * @var array $params
 */

use HttpSoft\Basis\Middleware\BodyParamsMiddleware;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Csrf\CsrfMiddleware;
use Yiisoft\DataResponse\Middleware\FormatDataResponseAsJson;
use Yiisoft\RequestProvider\RequestCatcherMiddleware;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;
use Yiisoft\Validator\ValidatorInterface;
use Yiisoft\Yii\Gii\Controller\DefaultController;
use Yiisoft\Yii\Middleware\CorsAllowAll;
use Yiisoft\Yii\Middleware\IpFilter;

if (empty($params['yiisoft/yii-gii']['enabled'])) {
    return [];
}

return [
    Group::create('/gii/api')
        ->withCors(CorsAllowAll::class)
        ->disableMiddleware(CsrfMiddleware::class)
        ->middleware(
            static fn(ResponseFactoryInterface $responseFactory, ValidatorInterface $validator): IpFilter
                => new IpFilter(
                    validator: $validator,
                    responseFactory: $responseFactory,
                    ipRanges: $params['yiisoft/yii-gii']['allowedIPs'],
                ),
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
                        ->middleware(BodyParamsMiddleware::class)
                        ->middleware(RequestCatcherMiddleware::class)
                        ->action([DefaultController::class, 'preview'])
                        ->name('preview'),
                    Route::post('/{generator}/generate')
                        ->middleware(BodyParamsMiddleware::class)
                        ->middleware(RequestCatcherMiddleware::class)
                        ->action([DefaultController::class, 'generate'])
                        ->name('generate'),
                    Route::post('/{generator}/diff')
                        ->middleware(BodyParamsMiddleware::class)
                        ->middleware(RequestCatcherMiddleware::class)
                        ->action([DefaultController::class, 'diff'])
                        ->name('diff'),
                ),
        ),
];
