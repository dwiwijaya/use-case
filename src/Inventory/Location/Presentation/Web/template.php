<?php

declare(strict_types=1);

use App\Inventory\Location\Domain\LocationInput;
use Yiisoft\FormModel\Field;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var list<string> $errors
 * @var string $status
 * @var LocationInput $form
 * @var list<array{id:string,code:string,name:string}> $locations
 * @var string|null $csrf
 * @var UrlGeneratorInterface $urlGenerator
 */

$this->setTitle('Inventory Locations');
$pageUrl = $urlGenerator->generate('inventory.locations');
?>

<div class="page-heading">
    <div>
        <p class="eyebrow">Inventory context</p>
        <h1>Lokasi gudang</h1>
        <p class="lead">Location dipisahkan dari shared item agar pengelolaan stok tetap fokus di inventory.</p>
    </div>
</div>

<?php if ($status !== ''): ?>
    <div class="alert success"><?= Html::encode($status) ?></div>
<?php endif; ?>

<?php foreach ($errors as $error): ?>
    <div class="alert danger"><?= Html::encode($error) ?></div>
<?php endforeach; ?>

<section class="page-grid compact">
    <article class="panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">Master location</p>
                <h2><?= $form->id === '' ? 'Tambah lokasi' : 'Edit lokasi' ?></h2>
            </div>
        </div>

        <form method="post" class="form-grid" action="<?= Html::encode($pageUrl) ?>">
            <?php if ($csrf !== null): ?>
                <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
            <?php endif; ?>
            <?= Field::hidden($form, 'id') ?>
            <?= Field::text($form, 'code')->required()->placeholder('JKT-01') ?>
            <?= Field::text($form, 'name')->required()->placeholder('Gudang Jakarta') ?>
            <?php Field::errorSummary($form) ?>
            <div class="form-actions full-width">
                <button class="button" type="submit">Simpan lokasi</button>
                <?php if ($form->id !== ''): ?>
                    <a class="button ghost" href="<?= Html::encode($pageUrl) ?>">Batal edit</a>
                <?php endif; ?>
            </div>
        </form>
    </article>

    <article class="panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">Current list</p>
                <h2>Daftar lokasi</h2>
            </div>
        </div>

        <table class="table">
            <thead>
            <tr>
                <th>Kode</th>
                <th>Nama</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($locations as $location): ?>
                <tr>
                    <td><?= Html::encode($location['code']) ?></td>
                    <td><?= Html::encode($location['name']) ?></td>
                    <td class="table-actions">
                        <a class="button ghost small" href="<?= Html::encode($pageUrl . '?edit=' . $location['id']) ?>">Edit</a>
                        <form method="post" action="<?= Html::encode($pageUrl) ?>">
                            <?php if ($csrf !== null): ?>
                                <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
                            <?php endif; ?>
                            <input type="hidden" name="operation" value="delete">
                            <input type="hidden" name="location[id]" value="<?= Html::encode($location['id']) ?>">
                            <button class="button danger small" type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </article>
</section>
