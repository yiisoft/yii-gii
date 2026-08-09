<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->disableComposerAutoloadPathScan()
    ->setFileExtensions(['php'])
    ->addPathToScan(__DIR__ . '/config', isDev: false)
    ->addPathToScan(__DIR__ . '/src', isDev: false)
    ->addPathToScan(__DIR__ . '/tests', isDev: true)
    // Active Record generator support is optional: `yiisoft/active-record` and `yiisoft/db` are only used
    // as an optional integration, not a hard requirement (see "suggest" in composer.json).
    ->ignoreErrorsOnPackages(['yiisoft/active-record'], [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackages(['yiisoft/db'], [ErrorType::SHADOW_DEPENDENCY]);
