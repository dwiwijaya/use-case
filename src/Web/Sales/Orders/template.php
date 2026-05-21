<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var list<string> $errors
 * @var string $status
 * @var int|null $selectedLocationId
 * @var array{
 *     customer_name:string,
 *     location_id:string,
 *     notes:string,
 *     lines:list<array{item_id:string,quantity:string}>
 * } $form
 * @var list<array{id:string,code:string,name:string}> $locations
 * @var list<array{id:string,sku:string,name:string,unit_id:string,unit_name:string,unit_symbol:string}> $items
 * @var list<array{item_id:string,item_name:string,sku:string,quantity:string,unit_symbol:string}> $stockRows
 * @var list<array{id:string,order_number:string,location_name:string,customer_name:string,ordered_at:string,total_items:string,notes:?string,items_summary:string}> $orders
 * @var string|null $csrf
 */

$this->setTitle('Sales Orders');
?>

<div class="page-heading">
    <div>
        <p class="eyebrow">Sales context</p>
        <h1>Order dan cek stok</h1>
        <p class="lead">Sales memakai shared item yang sama, lalu mengecek stok terhadap location inventory yang dipilih.</p>
    </div>
</div>

<?php if ($status !== ''): ?>
    <div class="alert success"><?= Html::encode($status) ?></div>
<?php endif; ?>

<?php foreach ($errors as $error): ?>
    <div class="alert danger"><?= Html::encode($error) ?></div>
<?php endforeach; ?>

<section class="page-grid">
    <article class="panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">Transaksi penjualan</p>
                <h2>Buat order baru</h2>
            </div>
        </div>

        <form method="post" class="form-grid">
            <?php if ($csrf !== null): ?>
                <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
            <?php endif; ?>

            <label>
                <span>Customer</span>
                <input type="text" name="customer_name" value="<?= Html::encode($form['customer_name']) ?>" placeholder="PT Sumber Makmur">
            </label>

            <label>
                <span>Lokasi stok</span>
                <select name="location_id">
                    <option value="">Pilih lokasi</option>
                    <?php foreach ($locations as $location): ?>
                        <option value="<?= Html::encode($location['id']) ?>" <?= $form['location_id'] === $location['id'] ? 'selected' : '' ?>>
                            <?= Html::encode($location['code']) ?> - <?= Html::encode($location['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="full-width">
                <span>Catatan</span>
                <textarea name="notes" rows="3" placeholder="Opsional"><?= Html::encode($form['notes']) ?></textarea>
            </label>

            <div class="subsection full-width">
                <h3>Baris order</h3>
                <div class="line-items">
                    <?php foreach ($form['lines'] as $index => $line): ?>
                        <div class="line-item-row">
                            <select name="lines[<?= $index ?>][item_id]">
                                <option value="">Pilih item</option>
                                <?php foreach ($items as $item): ?>
                                    <option value="<?= Html::encode($item['id']) ?>" <?= $line['item_id'] === $item['id'] ? 'selected' : '' ?>>
                                        <?= Html::encode($item['sku']) ?> - <?= Html::encode($item['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="number" min="1" name="lines[<?= $index ?>][quantity]" value="<?= Html::encode($line['quantity']) ?>" placeholder="Qty">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-actions full-width">
                <button class="button" type="submit">Simpan order</button>
            </div>
        </form>
    </article>

    <article class="panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">Cek stok item di gudang</p>
                <h2>Snapshot stok lokasi</h2>
            </div>
            <form method="get" class="inline-filter">
                <select name="location">
                    <option value="">Pilih lokasi</option>
                    <?php foreach ($locations as $location): ?>
                        <option value="<?= Html::encode($location['id']) ?>" <?= (string) $selectedLocationId === $location['id'] ? 'selected' : '' ?>>
                            <?= Html::encode($location['code']) ?> - <?= Html::encode($location['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button class="button ghost small" type="submit">Lihat stok</button>
            </form>
        </div>

        <table class="table">
            <thead>
            <tr>
                <th>SKU</th>
                <th>Item</th>
                <th>Qty tersedia</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($stockRows as $row): ?>
                <tr>
                    <td><?= Html::encode($row['sku']) ?></td>
                    <td><?= Html::encode($row['item_name']) ?> (<?= Html::encode($row['unit_symbol']) ?>)</td>
                    <td><?= Html::encode($row['quantity']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </article>
</section>

<section class="panel">
    <div class="panel-heading">
        <div>
            <p class="eyebrow">Rekap transaksi</p>
            <h2>Order history</h2>
        </div>
    </div>

    <table class="table">
        <thead>
        <tr>
            <th>Order</th>
            <th>Lokasi</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Items</th>
            <th>Catatan</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td>
                    <strong><?= Html::encode($order['order_number']) ?></strong><br>
                    <span class="muted"><?= Html::encode($order['ordered_at']) ?></span>
                </td>
                <td><?= Html::encode($order['location_name']) ?></td>
                <td><?= Html::encode($order['customer_name']) ?></td>
                <td><?= Html::encode($order['total_items']) ?></td>
                <td><?= Html::encode($order['items_summary']) ?></td>
                <td><?= Html::encode($order['notes'] ?? '-') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
