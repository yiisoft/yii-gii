<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii;

use Closure;

final class GeneratorProxy
{
    private ?GeneratorInterface $generator = null;

    /**
     * @psalm-param class-string<GeneratorInterface> $class
     */
    public function __construct(private readonly Closure $loader, private readonly string $class)
    {
    }

    /**
     * @return class-string<GeneratorInterface>
     */
    public function getClass(): string
    {
        return $this->class;
    }

    public function loadGenerator(): GeneratorInterface
    {
        if ($this->generator === null) {
            /**
             * @var GeneratorInterface ($this->loader)()
             */
            $loaded = ($this->loader)();
            $this->generator = $loaded;
        }
        return $this->generator;
    }
}
