<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii;

use Composer\Autoload\ClassLoader;
use LogicException;
use ReflectionClass;

use function dirname;
use function reset;
use function str_replace;
use function str_starts_with;
use function strlen;
use function substr;
use function trim;

/**
 * @internal
 */
final class Helper
{
    /**
     * Returns the file path matching the give namespace.
     *
     * @param string $namespace Namespace.
     *
     * @return string File path.
     */
    public static function getNamespacePath(string $namespace): string
    {
        $classLoaderReflection = new ReflectionClass(ClassLoader::class);
        $vendorDir = dirname($classLoaderReflection->getFileName(), 2);

        /**
         * @psalm-suppress UnresolvableInclude
         * @psalm-var array<string, list<string>> $map
         */
        $map = require "$vendorDir/composer/autoload_psr4.php";

        foreach ($map as $mapNamespace => $mapDirectories) {
            if (str_starts_with($namespace, trim($mapNamespace, '\\'))) {
                /** @var string $mapDirectory */
                $mapDirectory = reset($mapDirectories);
                return $mapDirectory . '/' . str_replace('\\', '/', substr($namespace, strlen($mapNamespace)));
            }
        }

        throw new LogicException("Invalid namespace: \"$namespace\".");
    }
}
