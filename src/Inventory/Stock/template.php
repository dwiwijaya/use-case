<?php

declare(strict_types=1);

use App\Inventory\Stock\StockInput;
use Yiisoft\FormModel\Field;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var list<string> $errors
 * @var string $status
 * @var int|null $selectedLocationId
 * @var StockInput $form
 * @var list<array{id:string,code:string,name:string}> $locations
 * @var list<array{id:string,sku:string,name:string,unit_id:string,unit_name:string,unit_symbol:string}> $items
 * @var list<array{id:string,location_id:string,location_name:string,item_id:string,sku:string,item_name:string,unit_symbol:string,quantity:string}> $stockRows
 * @var string|null $csrf
 * @var UrlGeneratorInterface $urlGenerator
 */

$this->setTitle('Inventory Stock');
$pageUrl = $urlGenerator->generate('inventory.stock');
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
        <p class="eyebrow">Inventory context</p>
        <h1>Stock per location</h1>
        <p class="lead">Tabel `item_location` menjadi penghubung utama antara shared item dan gudang inventory.</p>
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
                <p class="eyebrow">CRUD item ke location</p>
                <h2>Set stok item</h2>
            </div>
        </div>

        <form method="post" class="form-grid" action="<?= Html::encode($pageUrl) ?>">
            <?php if ($csrf !== null): ?>
                <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
            <?php endif; ?>
            <?= Field::select($form, 'locationId')->optionsData($locationOptions)->prompt('Pilih lokasi')->required() ?>
            <?= Field::select($form, 'itemId')->optionsData($itemOptions)->prompt('Pilih item')->required() ?>
            <?= Field::number($form, 'quantity')->min(0)->required() ?>
            <?= Field::errorSummary($form) ?>
            <div class="form-actions full-width">
                <button class="button" type="submit">Simpan stok</button>
            </div>
        </form>
    </article>

    <article class="panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">Informasi stok</p>
                <h2>Snapshot per lokasi</h2>
            </div>
            <form method="get" class="inline-filter" action="<?= Html::encode($pageUrl) ?>">
                <select name="location">
                    <option value="">Semua lokasi</option>
                    <?php foreach ($locations as $location): ?>
                        <option value="<?= Html::encode($location['id']) ?>" <?= (string) $selectedLocationId === $location['id'] ? 'selected' : '' ?>>
                            <?= Html::encode($location['code']) ?> - <?= Html::encode($location['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button class="button ghost small" type="submit">Filter</button>
            </form>
        </div>

        <table class="table">
            <thead>
            <tr>
                <th>Location</th>
                <th>SKU</th>
                <th>Item</th>
                <th>Qty</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($stockRows as $row): ?>
                <tr>
                    <td><?= Html::encode($row['location_name']) ?></td>
                    <td><?= Html::encode($row['sku']) ?></td>
                    <td><?= Html::encode($row['item_name']) ?> (<?= Html::encode($row['unit_symbol']) ?>)</td>
                    <td><?= Html::encode($row['quantity']) ?></td>
                    <td class="table-actions">
                        <form method="post" action="<?= Html::encode($pageUrl) ?>">
                            <?php if ($csrf !== null): ?>
                                <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
                            <?php endif; ?>
                            <input type="hidden" name="operation" value="delete">
                            <input type="hidden" name="stockDelete[id]" value="<?= Html::encode($row['id']) ?>">
                            <input type="hidden" name="currentLocation" value="<?= Html::encode($selectedLocationId === null ? '' : (string) $selectedLocationId) ?>">
                            <button class="button danger small" type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </article>
</section>
