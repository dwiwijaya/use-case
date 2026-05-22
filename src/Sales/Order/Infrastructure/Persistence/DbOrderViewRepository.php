<?php

declare(strict_types=1);

namespace App\Sales\Order\Infrastructure\Persistence;

use App\Sales\Order\ReadModel\OrderViewRepositoryInterface;
use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class DbOrderViewRepository implements OrderViewRepositoryInterface
{
    public function __construct(
        private ConnectionInterface $db,
    ) {}

    public function getSummary(): array
    {
        return [
            'orders' => (int) ($this->db->createCommand('SELECT COUNT(*) FROM sales_order')->queryScalar() ?? 0),
            'total_items_sold' => (int) ($this->db->createCommand('SELECT COALESCE(SUM(quantity), 0) FROM order_item')->queryScalar() ?? 0),
        ];
    }

    public function getRecentOrders(): array
    {
        return $this->db->createCommand(
            'SELECT so.order_number, l.name AS location_name, so.customer_name, so.ordered_at, so.total_items,
                GROUP_CONCAT(i.sku || " x" || oi.quantity, ", ") AS items_summary
            FROM sales_order so
            INNER JOIN location l ON l.id = so.location_id
            INNER JOIN order_item oi ON oi.order_id = so.id
            INNER JOIN item i ON i.id = oi.item_id
            GROUP BY so.id, so.order_number, l.name, so.customer_name, so.ordered_at, so.total_items
            ORDER BY so.id DESC
            LIMIT 5'
        )->queryAll();
    }

    public function getOrders(): array
    {
        return $this->db->createCommand(
            'SELECT so.id, so.order_number, l.name AS location_name, so.customer_name, so.ordered_at, so.total_items, so.notes,
                GROUP_CONCAT(i.sku || " x" || oi.quantity, ", ") AS items_summary
            FROM sales_order so
            INNER JOIN location l ON l.id = so.location_id
            INNER JOIN order_item oi ON oi.order_id = so.id
            INNER JOIN item i ON i.id = oi.item_id
            GROUP BY so.id, so.order_number, l.name, so.customer_name, so.ordered_at, so.total_items, so.notes
            ORDER BY so.id DESC'
        )->queryAll();
    }
}
