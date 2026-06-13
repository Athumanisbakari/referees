<?php
/**
 * Local vendor asset URLs for offline use
 */

function assetUrl(string $path): string
{
    return APP_URL . '/assets/' . ltrim($path, '/');
}

function vendorCss(string $file): string
{
    return assetUrl('vendor/' . ltrim($file, '/'));
}

function vendorJs(string $file): string
{
    return assetUrl('vendor/' . ltrim($file, '/'));
}

function renderVendorStyles(bool $includeLeaflet = false): void
{
    ?>
    <link href="<?= vendorCss('bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= vendorCss('bootstrap-icons/bootstrap-icons.min.css') ?>" rel="stylesheet">
    <?php if ($includeLeaflet): ?>
    <link href="<?= vendorCss('leaflet/leaflet.css') ?>" rel="stylesheet">
    <?php endif; ?>
    <link href="<?= assetUrl('css/style.css') ?>" rel="stylesheet">
    <?php
}

function renderVendorScripts(bool $includeLeaflet = false, bool $includeChart = false): void
{
    ?>
    <script src="<?= vendorJs('bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <?php if ($includeLeaflet): ?>
    <script src="<?= vendorJs('leaflet/leaflet.js') ?>"></script>
    <?php endif; ?>
    <?php if ($includeChart): ?>
    <script src="<?= vendorJs('chartjs/chart.umd.min.js') ?>"></script>
    <?php endif; ?>
    <?php
}

function renderPwaHead(): void
{
    ?>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#1a5276">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?= sanitize(APP_SHORT_NAME) ?>">
    <link rel="manifest" href="<?= APP_URL ?>/manifest.php">
    <link rel="icon" href="<?= assetUrl('icons/icon.svg') ?>" type="image/svg+xml">
    <link rel="apple-touch-icon" href="<?= assetUrl('icons/icon.svg') ?>">
    <?php
}

function renderPwaScripts(): void
{
    $appRoot = rtrim(parse_url(APP_URL, PHP_URL_PATH) ?: '', '/');
    ?>
    <script>window.APP_ROOT = <?= json_encode($appRoot) ?>;</script>
    <script src="<?= assetUrl('js/pwa.js') ?>"></script>
    <?php
}
