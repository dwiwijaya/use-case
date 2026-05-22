<?php

declare(strict_types=1);

use App\Catalog\Domain\Item\ItemInput;
use App\Catalog\Domain\Unit\UnitInput;
use Yiisoft\FormModel\Field;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var list<string> $errors
 * @var string $status
 * @var list<array{id:string,name:string,symbol:string}> $units
 * @var list<array{id:string,sku:string,name:string,unit_id:string,unit_name:string,unit_symbol:string}> $items
 * @var UnitInput $unitForm
 * @var ItemInput $itemForm
 * @var string|null $csrf
 * @var UrlGeneratorInterface $urlGenerator
 */

$this->setTitle('Catalog Items');
$pageUrl = $urlGenerator->generate('catalog.items');
$unitOptions = [];
foreach ($units as $unit) {
    $unitOptions[$unit['id']] = $unit['name'] . ' (' . $unit['symbol'] . ')';
}
?>

<div class="page-heading">
    <div>
        <p class="eyebrow">Shared context</p>
        <h1>Catalog item dan unit</h1>
        <p class="lead">Item diletakkan di shared context supaya inventory dan sales mengakses master yang sama.</p>
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
                <p class="eyebrow">Reference data</p>
                <h2><?= $unitForm->id === '' ? 'Tambah unit' : 'Edit unit' ?></h2>
            </div>
        </div>

        <form method="post" class="form-grid" action="<?= Html::encode($pageUrl) ?>">
            <?php if ($csrf !== null): ?>
                <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
            <?php endif; ?>
            <input type="hidden" name="entity" value="unit">
            <?= Field::hidden($unitForm, 'id') ?>
            <?= Field::text($unitForm, 'name')->required()->placeholder('Kilogram') ?>
            <?= Field::text($unitForm, 'symbol')->required()->placeholder('kg') ?>
            <?= Field::errorSummary($unitForm) ?>
            <div class="form-actions full-width">
                <button class="button" type="submit">Simpan unit</button>
                <?php if ($unitForm->id !== ''): ?>
                    <a class="button ghost" href="<?= Html::encode($pageUrl) ?>">Batal edit</a>
                <?php endif; ?>
            </div>
        </form>

        <table class="table">
            <thead>
            <tr>
                <th>Nama</th>
                <th>Simbol</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($units as $unit): ?>
                <tr>
                    <td><?= Html::encode($unit['name']) ?></td>
                    <td><?= Html::encode($unit['symbol']) ?></td>
                    <td class="table-actions">
                        <a class="button ghost small" href="<?= Html::encode($pageUrl . '?editUnit=' . $unit['id']) ?>">Edit</a>
                        <form method="post" action="<?= Html::encode($pageUrl) ?>">
                            <?php if ($csrf !== null): ?>
                                <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
                            <?php endif; ?>
                            <input type="hidden" name="entity" value="unit">
                            <input type="hidden" name="operation" value="delete">
                            <input type="hidden" name="unit[id]" value="<?= Html::encode($unit['id']) ?>">
                            <button class="button danger small" type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </article>

    <article class="panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">Shared master</p>
                <h2><?= $itemForm->id === '' ? 'Tambah item' : 'Edit item' ?></h2>
            </div>
        </div>

        <form method="post" class="form-grid" action="<?= Html::encode($pageUrl) ?>">
            <?php if ($csrf !== null): ?>
                <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
            <?php endif; ?>
            <input type="hidden" name="entity" value="item">
            <?= Field::hidden($itemForm, 'id') ?>
            <?= Field::text($itemForm, 'sku')->required()->placeholder('SKU-001') ?>
            <?= Field::text($itemForm, 'name')->required()->placeholder('Beras Premium') ?>
            <?= Field::select($itemForm, 'unitId')->optionsData($unitOptions)->prompt('Pilih unit')->required() ?>
            <?= Field::errorSummary($itemForm) ?>
            <div class="form-actions full-width">
                <button class="button" type="submit">Simpan item</button>
                <?php if ($itemForm->id !== ''): ?>
                    <a class="button ghost" href="<?= Html::encode($pageUrl) ?>">Batal edit</a>
                <?php endif; ?>
            </div>
        </form>

        <table class="table">
            <thead>
            <tr>
                <th>SKU</th>
                <th>Nama item</th>
                <th>Unit</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= Html::encode($item['sku']) ?></td>
                    <td><?= Html::encode($item['name']) ?></td>
                    <td><?= Html::encode($item['unit_name']) ?> (<?= Html::encode($item['unit_symbol']) ?>)</td>
                    <td class="table-actions">
                        <a class="button ghost small" href="<?= Html::encode($pageUrl . '?editItem=' . $item['id']) ?>">Edit</a>
                        <form method="post" action="<?= Html::encode($pageUrl) ?>">
                            <?php if ($csrf !== null): ?>
                                <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
                            <?php endif; ?>
                            <input type="hidden" name="entity" value="item">
                            <input type="hidden" name="operation" value="delete">
                            <input type="hidden" name="item[id]" value="<?= Html::encode($item['id']) ?>">
                            <button class="button danger small" type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </article>
</section>
