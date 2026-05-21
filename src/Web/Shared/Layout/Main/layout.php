<?php

declare(strict_types=1);

use Yiisoft\Html\Html;

/**
 * @var \App\Shared\ApplicationParams $applicationParams
 * @var Yiisoft\Aliases\Aliases $aliases
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var string $content
 * @var string|null $csrf
 * @var Yiisoft\View\WebView $this
 * @var Yiisoft\Router\CurrentRoute $currentRoute
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 */

$nav = [
    'home' => ['label' => 'Overview', 'url' => $urlGenerator->generate('home')],
    'catalog.items' => ['label' => 'Catalog', 'url' => $urlGenerator->generate('catalog.items')],
    'inventory.locations' => ['label' => 'Locations', 'url' => $urlGenerator->generate('inventory.locations')],
    'inventory.stock' => ['label' => 'Stock', 'url' => $urlGenerator->generate('inventory.stock')],
    'sales.orders' => ['label' => 'Sales', 'url' => $urlGenerator->generate('sales.orders')],
];

$this->beginPage()
?>
<!DOCTYPE html>
<html lang="<?= Html::encode($applicationParams->locale) ?>">
<head>
    <meta charset="<?= Html::encode($applicationParams->charset) ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="<?= $aliases->get('@baseUrl/favicon.svg') ?>" type="image/svg+xml">
    <title><?= Html::encode($this->getTitle()) ?></title>
    <style><?= file_get_contents($aliases->get('@root/assets/main/site.css')) ?></style>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="header">
    <div class="brand">
        <a href="<?= Html::encode($urlGenerator->generate('home')) ?>" class="brand-mark">Y3</a>
        <div>
            <strong><?= Html::encode($applicationParams->name) ?></strong>
            <p>Yii 3 inventory and sales proof of concept</p>
        </div>
    </div>

    <nav class="main-nav">
        <?php foreach ($nav as $routeName => $item): ?>
            <a href="<?= Html::encode($item['url']) ?>" class="<?= $currentRoute->getName() === $routeName ? 'active' : '' ?>">
                <?= Html::encode($item['label']) ?>
            </a>
        <?php endforeach; ?>
    </nav>
</div>

<div class="content">
    <div class="content_i">
        <?= $content ?>
    </div>
</div>

<div class="footer">
    <div class="footer_copyright">
        <a href="https://yiisoft.github.io/docs/" target="_blank" rel="noopener">
            © <?= date('Y') ?> <?= Html::encode($applicationParams->name) ?> · Built on Yii 3
        </a>
    </div>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
