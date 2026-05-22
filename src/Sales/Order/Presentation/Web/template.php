<?php

declare(strict_types=1);

use App\Sales\Order\Domain\OrderInput;
use Yiisoft\FormModel\Field;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var list<string> $errors
 * @var string $status
 * @var int|null $selectedLocationId
 * @var OrderInput $form
 * @var list<array{id:string,code:string,name:string}> $locations
 * @var list<array{id:string,sku:string,name:string,unit_id:string,unit_name:string,unit_symbol:string}> $items
 * @var list<array{item_id:string,item_name:string,sku:string,quantity:string,unit_symbol:string}> $stockRows
 * @var list<array{id:string,order_number:string,location_name:string,customer_name:string,ordered_at:string,total_items:string,notes:?string,items_summary:string}> $orders
 * @var string|null $csrf
 * @var UrlGeneratorInterface $urlGenerator
 */

$this->setTitle('Sales Orders');
$pageUrl = $urlGenerator->generate('sales.orders');
$locationOptions = [];
foreach ($locations as $location) {
    $locationOptions[$location['id']] = $location['code'] . ' - ' . $location['name'];
}
$itemOptions = [];
foreach ($items as $item) {
    $itemOptions[$item['id']] = $item['sku'] . ' - ' . $item['name'];
}
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

        <form method="post" class="form-grid" action="<?= Html::encode($pageUrl) ?>">
            <?php if ($csrf !== null): ?>
                <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
            <?php endif; ?>

            <?= Field::text($form, 'customerName')->required()->placeholder('PT Sumber Makmur') ?>
            <?= Field::select($form, 'locationId')->optionsData($locationOptions)->prompt('Pilih lokasi')->required() ?>
            <?= Field::textarea($form, 'notes')->rows(3)->placeholder('Opsional') ?>
            <?= Field::errorSummary($form) ?>

            <div class="subsection full-width">
                <h3>Baris order</h3>
                <div class="line-items">
                    <?php foreach ($form->lines as $index => $_line): ?>
                        <div class="line-item-row">
                            <?= Field::select($form, "lines[$index][item_id]")->optionsData($itemOptions)->prompt('Pilih item') ?>
                            <?= Field::number($form, "lines[$index][quantity]")->min(1)->placeholder('Qty') ?>
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
            <form method="get" class="inline-filter" action="<?= Html::encode($pageUrl) ?>">
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
