<?php

declare(strict_types=1);

namespace App\Migration;

use Yiisoft\Db\Constraint\ForeignKey;
use Yiisoft\Db\Constant\IndexType;
use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;
use Yiisoft\Db\Migration\TransactionalMigrationInterface;

final class M260521120000InitialInventorySalesSchema implements RevertibleMigrationInterface, TransactionalMigrationInterface
{
    public function up(MigrationBuilder $b): void
    {
        $column = $b->columnBuilder();

        $b->createTable('unit', [
            'id' => $column::primaryKey(),
            'name' => $column::string()->notNull()->unique(),
            'symbol' => $column::string()->notNull()->unique(),
        ]);

        $b->createTable('item', [
            'id' => $column::primaryKey(),
            'sku' => $column::string()->notNull()->unique(),
            'name' => $column::string()->notNull(),
            'unit_id' => $column::integer()->notNull()->reference(
                new ForeignKey(
                    foreignTableName: 'unit',
                    foreignColumnNames: ['id']
                )
            ),
        ]);

        $b->createTable('location', [
            'id' => $column::primaryKey(),
            'code' => $column::string()->notNull()->unique(),
            'name' => $column::string()->notNull(),
        ]);

        $b->createTable('item_location', [
            'id' => $column::primaryKey(),
            'item_id' => $column::integer()->notNull()->reference(
                new ForeignKey(
                    foreignTableName: 'item',
                    foreignColumnNames: ['id']
                )
            ),
            'location_id' => $column::integer()->notNull()->reference(
                new ForeignKey(
                    foreignTableName: 'location',
                    foreignColumnNames: ['id']
                )
            ),
            'quantity' => $column::integer()->notNull()->defaultValue(0),
        ]);
        $b->createIndex('item_location', 'ux_item_location_unique', ['item_id', 'location_id'], IndexType::UNIQUE);

        $b->createTable('sales_order', [
            'id' => $column::primaryKey(),
            'order_number' => $column::string()->notNull()->unique(),
            'location_id' => $column::integer()->notNull()->reference(
                new ForeignKey(
                    foreignTableName: 'location',
                    foreignColumnNames: ['id']
                )
            ),
            'customer_name' => $column::string()->notNull(),
            'ordered_at' => $column::dateTime()->notNull(),
            'total_items' => $column::integer()->notNull()->defaultValue(0),
            'notes' => $column::text(),
        ]);

        $b->createTable('order_item', [
            'id' => $column::primaryKey(),
            'order_id' => $column::integer()->notNull()->reference(
                new ForeignKey(
                    foreignTableName: 'sales_order',
                    foreignColumnNames: ['id']
                )
            ),
            'item_id' => $column::integer()->notNull()->reference(
                new ForeignKey(
                    foreignTableName: 'item',
                    foreignColumnNames: ['id']
                )
            ),
            'quantity' => $column::integer()->notNull(),
        ]);

        $b->batchInsert('unit', ['name', 'symbol'], [
            ['Kilogram', 'kg'],
            ['Liter', 'ltr'],
            ['Piece', 'pcs'],
        ]);

        $b->batchInsert('item', ['sku', 'name', 'unit_id'], [
            ['SKU-001', 'Beras Premium', 1],
            ['SKU-002', 'Minyak Goreng', 2],
            ['SKU-003', 'Gula Pasir', 1],
            ['SKU-004', 'Dus Air Mineral', 3],
        ]);

        $b->batchInsert('location', ['code', 'name'], [
            ['JKT-01', 'Gudang Jakarta'],
            ['SBY-01', 'Gudang Surabaya'],
        ]);

        $b->batchInsert('item_location', ['item_id', 'location_id', 'quantity'], [
            [1, 1, 120],
            [2, 1, 80],
            [3, 1, 60],
            [4, 1, 24],
            [1, 2, 75],
            [2, 2, 40],
            [4, 2, 16],
        ]);
    }

    public function down(MigrationBuilder $b): void
    {
        $b->dropTable('order_item');
        $b->dropTable('sales_order');
        $b->dropTable('item_location');
        $b->dropTable('location');
        $b->dropTable('item');
        $b->dropTable('unit');
    }
}
