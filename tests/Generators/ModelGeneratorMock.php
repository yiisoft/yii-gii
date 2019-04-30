<?php

namespace Yiisoft\Yii\Gii\Tests\Generators;

use Yiisoft\Yii\Gii\Generators\Model\Generator;

/**
 * Just a mock for testing porpouses.
 */
class ModelGeneratorMock extends Generator
{
    public function publicGenerateClassName($tableName, $useSchemaName = null)
    {
        return $this->generateClassName($tableName, $useSchemaName);
    }
}
