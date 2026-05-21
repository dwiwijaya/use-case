<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var list<string> $errors
 * @var string $status
 * @var list<array{id:string,name:string,symbol:string}> $units
 * @var list<array{id:string,sku:string,name:string,unit_id:string,unit_name:string,unit_symbol:string}> $items
 * @var array{id:string,name:string,symbol:string} $unitForm
 * @var array{id:string,sku:string,name:string,unit_id:string} $itemForm
 * @var string|null $csrf
 */

$this->setTitle('Catalog Items');
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
                <h2><?= $unitForm['id'] === '' ? 'Tambah unit' : 'Edit unit' ?></h2>
            </div>
        </div>

        <form method="post" class="form-grid">
            <?php if ($csrf !== null): ?>
                <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
            <?php endif; ?>
            <input type="hidden" name="entity" value="unit">
            <input type="hidden" name="id" value="<?= Html::encode($unitForm['id']) ?>">

            <label>
                <span>Nama unit</span>
                <input type="text" name="name" value="<?= Html::encode($unitForm['name']) ?>" placeholder="Kilogram">
            </label>

            <label>
                <span>Simbol</span>
                <input type="text" name="symbol" value="<?= Html::encode($unitForm['symbol']) ?>" placeholder="kg">
            </label>

            <div class="form-actions full-width">
                <button class="button" type="submit">Simpan unit</button>
                <?php if ($unitForm['id'] !== ''): ?>
                    <a class="button ghost" href="/catalog/items">Batal edit</a>
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
                        <a class="button ghost small" href="/catalog/items?editUnit=<?= Html::encode($unit['id']) ?>">Edit</a>
                        <form method="post">
                            <?php if ($csrf !== null): ?>
                                <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
                            <?php endif; ?>
                            <input type="hidden" name="entity" value="unit">
                            <input type="hidden" name="operation" value="delete">
                            <input type="hidden" name="id" value="<?= Html::encode($unit['id']) ?>">
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
                <h2><?= $itemForm['id'] === '' ? 'Tambah item' : 'Edit item' ?></h2>
            </div>
        </div>

        <form method="post" class="form-grid">
            <?php if ($csrf !== null): ?>
                <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
            <?php endif; ?>
            <input type="hidden" name="entity" value="item">
            <input type="hidden" name="id" value="<?= Html::encode($itemForm['id']) ?>">

            <label>
                <span>SKU</span>
                <input type="text" name="sku" value="<?= Html::encode($itemForm['sku']) ?>" placeholder="SKU-001">
            </label>

            <label>
                <span>Nama item</span>
                <input type="text" name="name" value="<?= Html::encode($itemForm['name']) ?>" placeholder="Beras Premium">
            </label>

            <label class="full-width">
                <span>Unit</span>
                <select name="unit_id">
                    <option value="">Pilih unit</option>
                    <?php foreach ($units as $unit): ?>
                        <option value="<?= Html::encode($unit['id']) ?>" <?= $itemForm['unit_id'] === $unit['id'] ? 'selected' : '' ?>>
                            <?= Html::encode($unit['name']) ?> (<?= Html::encode($unit['symbol']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <div class="form-actions full-width">
                <button class="button" type="submit">Simpan item</button>
                <?php if ($itemForm['id'] !== ''): ?>
                    <a class="button ghost" href="/catalog/items">Batal edit</a>
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
                        <a class="button ghost small" href="/catalog/items?editItem=<?= Html::encode($item['id']) ?>">Edit</a>
                        <form method="post">
                            <?php if ($csrf !== null): ?>
                                <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
                            <?php endif; ?>
                            <input type="hidden" name="entity" value="item">
                            <input type="hidden" name="operation" value="delete">
                            <input type="hidden" name="id" value="<?= Html::encode($item['id']) ?>">
                            <button class="button danger small" type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </article>
</section>
