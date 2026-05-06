<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Tests\Generators\ActiveRecord;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yiisoft\Yii\Gii\Generator\ActiveRecord\ArHelper;

final class ArHelperTest extends TestCase
{
    public static function getRelationNameProvider(): array
    {
        return [
            // Identity field names replaced by $defaultName
            'id' => [['id'], 'user', 'user'],
            'uuid' => [['uuid'], 'Profile', 'profile'],
            'key' => [['key'], 'CONFIG', 'config'],
            'code' => [['code'], 'country_region', 'countryRegion'],

            // Identity suffixes (_id, _uuid, _key, _code) are removed
            '_id' => [['user_id'], 'post', 'user'],
            '_uuid' => [['role_uuid'], 'post', 'role'],
            '_key' => [['option_key'], 'post', 'option'],
            '_code' => [['lang_code'], 'post', 'lang'],

            // Different naming conventions
            'lowercase' => [['username'], 'user', 'username'],
            'UPPERCASE' => [['username'], 'user', 'username'],
            'snake_case' => [['first_name'], 'user', 'firstName'],
            'PascalCase' => [['LastName'], 'user', 'lastName'],
            'uppercase ID' => [['ID'], 'user', 'user'],
            'mixed case' => [['Profile_Id'], 'user', 'profile'],
            'SCREAMING_SNAKE_CASE' => [['FIRST_NAME'], 'user', 'firstName'],

            // Multiple columns are joined and camelCased
            'two snake_case columns' => [['first_name', 'last_name'], 'user', 'firstNameLastName'],
            'id + plain' => [['id', 'type'], 'user', 'userType'],
            'two _id suffix columns' => [['user_id', 'role_id'], 'user', 'userRole'],
            'three mixed columns' => [['id', 'category_id', 'status'], 'post', 'postCategoryStatus'],
        ];
    }

    #[DataProvider('getRelationNameProvider')]
    public function testGetRelationName(array $columnNames, string $defaultName, string $expected): void
    {
        $this->assertSame($expected, ArHelper::getRelationName($columnNames, $defaultName));
    }

    public static function getRelationNameNoStripProvider(): array
    {
        return [
            // Identity field names are NOT replaced when $stripIdentitySuffix = false
            'id no-strip' => [['id'], 'user', 'id'],
            'uuid no-strip' => [['uuid'], 'Profile', 'uuid'],

            // Identity suffixes are NOT removed when $stripIdentitySuffix = false
            '_id no-strip' => [['user_id'], 'post', 'userId'],
            '_uuid no-strip' => [['user_uuid'], 'post', 'userUuid'],
            '_key no-strip' => [['option_key'], 'post', 'optionKey'],
            '_code no-strip' => [['lang_code'], 'post', 'langCode'],

            // Regular columns are camelCased as usual
            'snake_case no-strip' => [['first_name'], 'user', 'firstName'],
            'plain no-strip' => [['username'], 'user', 'username'],

            // Collision-resolution use case: two colliding FKs get distinct names
            'user_id collision' => [['user_id'], 'user', 'userId'],
            'user_uuid collision' => [['user_uuid'], 'user', 'userUuid'],
        ];
    }

    #[DataProvider('getRelationNameNoStripProvider')]
    public function testGetRelationNameNoStrip(array $columnNames, string $defaultName, string $expected): void
    {
        $this->assertSame($expected, ArHelper::getRelationName($columnNames, $defaultName, false));
    }
}
