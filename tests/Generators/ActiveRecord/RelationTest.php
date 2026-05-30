<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Tests\Generators\ActiveRecord;

use PHPUnit\Framework\TestCase;
use Yiisoft\Db\Constraint\Index;
use Yiisoft\Db\Schema\TableSchema;
use Yiisoft\Yii\Gii\Generator\ActiveRecord\Relation;

final class RelationTest extends TestCase
{
    public function testHasOneRelation(): void
    {
        $user = $this->makeTableSchema('user', ['id'], [['profile_id']]);
        $userProfile = $this->makeTableSchema('user_profile', ['id'], [['user_id']]);

        $relation = new Relation($user, ['profile_id'], $userProfile, ['id']);

        $this->assertSame('hasOne', $relation->getRelationMethod());
        $this->assertSame('profile', $relation->getName());
        $this->assertSame('user', $relation->getInverseOf());
        $this->assertSame('UserProfile', $relation->getRelatedModel());
        $this->assertSame(['id' => 'profile_id'], $relation->getLink());
        $this->assertSame('getProfile', $relation->getGetterMethodName());
        $this->assertSame('getProfileQuery', $relation->getQueryMethodName());
        $this->assertSame('?UserProfile', $relation->getGetterReturnType());
    }

    public function testHasManyRelation(): void
    {
        $user = $this->makeTableSchema('user', ['id']);
        $post = $this->makeTableSchema('post', ['id']);

        $relation = new Relation($user, ['id'], $post, ['created_by']);

        $this->assertSame('hasMany', $relation->getRelationMethod());
        $this->assertSame('posts', $relation->getName());
        $this->assertSame('createdBy', $relation->getInverseOf());
        $this->assertSame('Post', $relation->getRelatedModel());
        $this->assertSame(['created_by' => 'id'], $relation->getLink());
        $this->assertSame('getPosts', $relation->getGetterMethodName());
        $this->assertSame('getPostsQuery', $relation->getQueryMethodName());
        $this->assertSame('array', $relation->getGetterReturnType());
    }

    public function testHasOneViaUniqueIndex(): void
    {
        $user = $this->makeTableSchema('user', uniques: [['id']]);
        $userProfile = $this->makeTableSchema('user_profile', ['id'], [['user_id']]);

        $relation = new Relation($userProfile, ['user_id'], $user, ['id']);

        $this->assertSame('hasOne', $relation->getRelationMethod());
        $this->assertSame('user', $relation->getName());
        $this->assertSame('userProfile', $relation->getInverseOf());
        $this->assertSame('User', $relation->getRelatedModel());
        $this->assertSame(['id' => 'user_id'], $relation->getLink());
        $this->assertSame('getUser', $relation->getGetterMethodName());
        $this->assertSame('getUserQuery', $relation->getQueryMethodName());
        $this->assertSame('?User', $relation->getGetterReturnType());
    }

    public function testInverseHasOneRelation(): void
    {
        $user = $this->makeTableSchema('user', ['id'], [['profile_id']]);
        $userProfile = $this->makeTableSchema('user_profile', ['id']);

        $relation = new Relation($userProfile, ['id'], $user, ['profile_id']);

        $this->assertSame('hasOne', $relation->getRelationMethod());
        $this->assertSame('user', $relation->getName());
        $this->assertSame('profile', $relation->getInverseOf());
        $this->assertSame('User', $relation->getRelatedModel());
        $this->assertSame(['profile_id' => 'id'], $relation->getLink());
        $this->assertSame('getUser', $relation->getGetterMethodName());
        $this->assertSame('getUserQuery', $relation->getQueryMethodName());
        $this->assertSame('?User', $relation->getGetterReturnType());
    }

    public function testCompositeForeignKey(): void
    {
        $orderItem = $this->makeTableSchema('order_item', ['order_id', 'item_id']);
        $invoiceItem = $this->makeTableSchema('invoice_item', ['id']);

        $relation = new Relation($orderItem, ['order_id', 'item_id'], $invoiceItem, ['order_id', 'item_id']);

        $this->assertSame('hasMany', $relation->getRelationMethod());
        $this->assertSame('orderItems', $relation->getName());
        $this->assertSame('orderItem', $relation->getInverseOf());
        $this->assertSame('InvoiceItem', $relation->getRelatedModel());
        $this->assertSame(['order_id' => 'order_id', 'item_id' => 'item_id'], $relation->getLink());
        $this->assertSame('getOrderItems', $relation->getGetterMethodName());
        $this->assertSame('getOrderItemsQuery', $relation->getQueryMethodName());
        $this->assertSame('array', $relation->getGetterReturnType());
    }

    public function testGetRelationNameFromCompositeColumnName(): void
    {
        $package = $this->makeTableSchema('package');
        $orderItem = $this->makeTableSchema('order_item', ['id']);
        $relation = new Relation($package, ['order_item_id'], $orderItem, ['id']);

        $this->assertSame('orderItem', $relation->getName());
        $this->assertSame('packages', $relation->getInverseOf());
    }

    /**
     * Builds a TableSchema with an optional primary key and optional unique indexes.
     *
     * @param string $tableName Table name
     * @param string[] $primaryKey Primary-key column names (empty = no PK index)
     * @param array $uniques Each element is a list of column names forming a unique index
     */
    private function makeTableSchema(string $tableName, array $primaryKey = [], array $uniques = []): TableSchema
    {
        $indexes = [];

        if ($primaryKey !== []) {
            $indexes[] = new Index('PRIMARY', $primaryKey, true, true);
        }

        foreach ($uniques as $i => $columns) {
            $indexes[] = new Index("uq_$i", $columns, true);
        }

        $schema = new TableSchema($tableName);

        if ($indexes !== []) {
            $schema->indexes(...$indexes);
        }

        return $schema;
    }
}
