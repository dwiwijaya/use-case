<?php

declare(strict_types=1);

namespace App\Sales\Order\Infrastructure\Persistence;

use App\Sales\Order\Domain\Order;
use App\Sales\Order\Domain\OrderRepositoryInterface;
use Yiisoft\Db\Connection\ConnectionInterface;

use function implode;
use function sprintf;

final readonly class DbOrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        private ConnectionInterface $db,
    ) {}

    public function create(Order $order): void
    {
        $aggregated = [];
        foreach ($order->lines as $line) {
            $aggregated[$line->itemId] = ($aggregated[$line->itemId] ?? 0) + $line->quantity;
        }

        $this->db->transaction(function () use ($order, $aggregated): void {
            $orderId = $this->db->createCommand()->insertReturningPks('sales_order', [
                'order_number' => $order->orderNumber,
                'location_id' => $order->locationId,
                'customer_name' => $order->customerName,
                'ordered_at' => $order->orderedAt,
                'total_items' => $order->totalItems,
                'notes' => $order->notes,
            ])['id'];

            foreach ($aggregated as $itemId => $quantity) {
                $this->db->createCommand()->insert('order_item', [
                    'order_id' => $orderId,
                    'item_id' => $itemId,
                    'quantity' => $quantity,
                ])->execute();

                $this->db->createCommand(
                    'UPDATE item_location
                    SET quantity = quantity - :quantity
                    WHERE location_id = :locationId AND item_id = :itemId',
                    [':quantity' => $quantity, ':locationId' => $order->locationId, ':itemId' => $itemId]
                )->execute();
            }
        });
    }

    /**
     * @param list<int> $itemIds
     * @return array<int,int>
     */
    public function getAvailableStock(int $locationId, array $itemIds): array
    {
        $placeholders = [];
        $params = [':locationId' => $locationId];
        foreach ($itemIds as $index => $itemId) {
            $placeholders[] = ':item' . $index;
            $params[':item' . $index] = $itemId;
        }

        if ($placeholders === []) {
            return [];
        }

        $rows = $this->db->createCommand(
            sprintf(
                'SELECT i.id, i.sku, COALESCE(il.quantity, 0) AS quantity
                FROM item i
                LEFT JOIN item_location il ON il.item_id = i.id AND il.location_id = :locationId
                WHERE i.id IN (%s)',
                implode(', ', $placeholders)
            ),
            $params
        )->queryAll();

        $stock = [];
        foreach ($rows as $row) {
            $stock[(int) $row['id']] = (int) $row['quantity'];
        }

        return $stock;
    }

    public function locationExists(int $locationId): bool
    {
        $exists = $this->db->createCommand(
            'SELECT id FROM location WHERE id = :id',
            [':id' => $locationId]
        )->queryScalar();

        return $exists !== null && $exists !== false;
    }
}
