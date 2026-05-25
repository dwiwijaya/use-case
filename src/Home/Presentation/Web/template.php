<?php

declare(strict_types=1);

use App\Shared\ApplicationParams;
use Yiisoft\Html\Html;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var ApplicationParams $applicationParams
 * @var array{items:int,units:int} $catalogSummary
 * @var array{locations:int,stock_rows:int,total_quantity:int} $inventorySummary
 * @var list<array{id:string,name:string,stock_rows:string,total_quantity:string}> $stockByLocation
 * @var array{orders:int,total_items_sold:int} $salesSummary
 * @var list<array{order_number:string,location_name:string,customer_name:string,ordered_at:string,total_items:string,items_summary:string}> $recentOrders
 */

$this->setTitle('Yii3 Inventory + Sales POC');
?>

<section class="stats-grid">
    <article class="stat-card">
        <span class="stat-label">Catalog</span>
        <strong class="stat-value"><?= Html::encode((string) $catalogSummary['items']) ?></strong>
        <span class="stat-note"><?= Html::encode((string) $catalogSummary['units']) ?> unit aktif</span>
    </article>
    <article class="stat-card">
        <span class="stat-label">Inventory</span>
        <strong class="stat-value"><?= Html::encode((string) $inventorySummary['total_quantity']) ?></strong>
        <span class="stat-note">
            <?= Html::encode((string) $inventorySummary['locations']) ?> lokasi,
            <?= Html::encode((string) $inventorySummary['stock_rows']) ?> baris stok
        </span>
    </article>
    <article class="stat-card">
        <span class="stat-label">Sales</span>
        <strong class="stat-value"><?= Html::encode((string) $salesSummary['orders']) ?></strong>
        <span class="stat-note"><?= Html::encode((string) $salesSummary['total_items_sold']) ?> item terjual</span>
    </article>
</section>

<section class="page-grid">
    <article class="panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">Shared context</p>
                <h2>Stock per location</h2>
            </div>
            <a class="button ghost" href="/inventory/stock">Kelola stok</a>
        </div>

        <table class="table">
            <thead>
            <tr>
                <th>Location</th>
                <th>SKU aktif</th>
                <th>Total qty</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($stockByLocation as $row): ?>
                <tr>
                    <td><?= Html::encode($row['name']) ?></td>
                    <td><?= Html::encode($row['stock_rows']) ?></td>
                    <td><?= Html::encode($row['total_quantity']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </article>

    <article class="panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">Transactional context</p>
                <h2>Recent orders</h2>
            </div>
            <a class="button ghost" href="/sales/orders">Buka sales</a>
        </div>

        <div class="stack">
            <?php foreach ($recentOrders as $order): ?>
                <div class="summary-card">
                    <div class="summary-row">
                        <strong><?= Html::encode($order['order_number']) ?></strong>
                        <span><?= Html::encode($order['ordered_at']) ?></span>
                    </div>
                    <div class="summary-row muted">
                        <span><?= Html::encode($order['location_name']) ?> · <?= Html::encode($order['customer_name']) ?></span>
                        <span><?= Html::encode($order['total_items']) ?> item</span>
                    </div>
                    <p class="summary-text"><?= Html::encode($order['items_summary']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </article>
</section>
