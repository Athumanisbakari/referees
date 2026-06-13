<?php
$currentLang = getCurrentLang();
?>
<div class="dropdown language-switcher">
    <button class="btn btn-link dropdown-toggle d-flex align-items-center gap-1" data-bs-toggle="dropdown" aria-label="<?= __('nav.language') ?>">
        <i class="bi bi-translate"></i>
        <span class="d-none d-md-inline"><?= sanitize(__('lang.' . $currentLang)) ?></span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li><span class="dropdown-item-text text-muted small"><?= __('nav.language') ?></span></li>
        <li><hr class="dropdown-divider"></li>
        <?php foreach (SUPPORTED_LANGUAGES as $lang): ?>
            <li>
                <a class="dropdown-item<?= $currentLang === $lang ? ' active' : '' ?>" href="<?= languageUrl($lang) ?>">
                    <?= sanitize(__('lang.' . $lang)) ?>
                    <?php if ($currentLang === $lang): ?>
                        <i class="bi bi-check2 ms-1"></i>
                    <?php endif; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
