<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Tests\Validator;

use PHPUnit\Framework\TestCase;
use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\ActiveRecord\ActiveRecordInterface;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\ValidationContext;
use Yiisoft\Yii\Gii\Validator\ClassExistsHandler;
use Yiisoft\Yii\Gii\Validator\ClassExistsRule;
use stdClass;

final class ClassExistsHandlerTest extends TestCase
{
    public function testValidClassWithoutParentConstraint(): void
    {
        $rule = new ClassExistsRule();
        $result = $this->validate(ActiveRecord::class, $rule);

        $this->assertTrue($result->isValid());
    }

    public function testNonExistentClassFails(): void
    {
        $rule = new ClassExistsRule();
        $result = $this->validate('NonExistent\\ClassName', $rule);

        $this->assertFalse($result->isValid());
        $this->assertStringContainsString('does not exist', $result->getErrorMessages()[0]);
    }

    public function testNonStringValueFails(): void
    {
        $rule = new ClassExistsRule();
        $result = $this->validate(42, $rule);

        $this->assertFalse($result->isValid());
        $this->assertStringContainsString('must be a string', $result->getErrorMessages()[0]);
    }

    public function testValidSubclassPassesParentConstraint(): void
    {
        $rule = new ClassExistsRule(ActiveRecordInterface::class);
        $result = $this->validate(ActiveRecord::class, $rule);

        $this->assertTrue($result->isValid());
    }

    public function testNonSubclassFailsParentConstraint(): void
    {
        $rule = new ClassExistsRule(ActiveRecordInterface::class);
        $result = $this->validate(stdClass::class, $rule);

        $this->assertFalse($result->isValid());
        $this->assertStringContainsString('is not a subclass of', $result->getErrorMessages()[0]);
        $this->assertStringContainsString(ActiveRecordInterface::class, $result->getErrorMessages()[0]);
    }

    public function testNonExistentClassWithParentConstraintFails(): void
    {
        $rule = new ClassExistsRule(ActiveRecordInterface::class);
        $result = $this->validate('NonExistent\\ClassName', $rule);

        $this->assertFalse($result->isValid());
        $this->assertStringContainsString('does not exist', $result->getErrorMessages()[0]);
    }

    public function testParentClassIsNullByDefault(): void
    {
        $rule = new ClassExistsRule();

        $this->assertNull($rule->parent);
    }

    public function testParentClassIsStoredOnRule(): void
    {
        $rule = new ClassExistsRule(ActiveRecordInterface::class);

        $this->assertSame(ActiveRecordInterface::class, $rule->parent);
    }

    private function validate(mixed $value, ClassExistsRule $rule): Result
    {
        $handler = new ClassExistsHandler();
        $context = new ValidationContext();
        return $handler->validate($value, $rule, $context);
    }
}
