<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var list<string> $errors
 * @var string $status
 * @var array{id:string,code:string,name:string} $form
 * @var list<array{id:string,code:string,name:string}> $locations
 * @var string|null $csrf
 */

$this->setTitle('Inventory Locations');
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
                <h2><?= $form['id'] === '' ? 'Tambah lokasi' : 'Edit lokasi' ?></h2>
            </div>
        </div>

        <form method="post" class="form-grid">
            <?php if ($csrf !== null): ?>
                <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
            <?php endif; ?>
            <input type="hidden" name="id" value="<?= Html::encode($form['id']) ?>">

            <label>
                <span>Kode lokasi</span>
                <input type="text" name="code" value="<?= Html::encode($form['code']) ?>" placeholder="JKT-01">
            </label>

            <label>
                <span>Nama lokasi</span>
                <input type="text" name="name" value="<?= Html::encode($form['name']) ?>" placeholder="Gudang Jakarta">
            </label>

            <div class="form-actions full-width">
                <button class="button" type="submit">Simpan lokasi</button>
                <?php if ($form['id'] !== ''): ?>
                    <a class="button ghost" href="/inventory/locations">Batal edit</a>
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
                        <a class="button ghost small" href="/inventory/locations?edit=<?= Html::encode($location['id']) ?>">Edit</a>
                        <form method="post">
                            <?php if ($csrf !== null): ?>
                                <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
                            <?php endif; ?>
                            <input type="hidden" name="operation" value="delete">
                            <input type="hidden" name="id" value="<?= Html::encode($location['id']) ?>">
                            <button class="button danger small" type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </article>
</section>
