<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii;

use Closure;

class GeneratorProxy
{
    private ?GeneratorInterface $generator = null;

    /**
     * @psalm-param class-string<GeneratorCommandInterface> $class
     */
    public function __construct(private Closure $loader, private string $class)
    {
    }

    /**
     * @return class-string<GeneratorCommandInterface>
     */
    public function getClass(): string
    {
        return $this->class;
    }

    public function loadGenerator(): GeneratorInterface
    {
        return $this->generator ??= ($this->loader)();
    }
}
