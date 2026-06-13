<?php
$mobileNavItems = getMobileNavItems($userRole);
?>
<nav class="mobile-bottom-nav d-md-none" aria-label="<?= __('mobile.navigation') ?>">
    <?php foreach ($mobileNavItems as $item): ?>
        <?php if (isset($item['action']) && $item['action'] === 'menu'): ?>
            <button type="button" class="mobile-nav-item" id="mobileMenuButton" aria-label="<?= __('mobile.menu') ?>">
                <i class="bi <?= $item['icon'] ?>"></i>
                <span><?= __($item['label_key']) ?></span>
            </button>
        <?php else: ?>
            <a href="<?= APP_URL . $item['url'] ?>"
               class="mobile-nav-item<?= isNavItemActive($item) ? ' active' : '' ?>">
                <i class="bi <?= $item['icon'] ?>"></i>
                <span><?= __($item['label_key']) ?></span>
            </a>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>

<div id="pwaInstallBanner" class="pwa-install-banner">
    <div>
        <strong><?= __('mobile.install_title') ?></strong>
        <p class="mb-0 small"><?= __('mobile.install_body') ?></p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-light" id="pwaInstallDismiss"><?= __('mobile.install_dismiss') ?></button>
        <button type="button" class="btn btn-sm btn-warning" id="pwaInstallButton"><?= __('mobile.install_action') ?></button>
    </div>
</div>
