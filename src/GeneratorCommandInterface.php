<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii;

interface GeneratorCommandInterface
{
    /**
     * @return list<string>
     */
    public static function getAttributes(): array;

    /**
     * @return array<string, string>
     */
    public static function getAttributeLabels(): array;

    /**
     * @return array<string, string>
     */
    public static function getHints(): array;

    public function getTemplate(): string;
}
