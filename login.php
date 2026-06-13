<?php
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    redirect(APP_URL . '/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/includes/auth.php';

    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = __('login.error.invalid_request');
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (login($username, $password)) {
            redirect(APP_URL . '/dashboard.php');
        } else {
            $error = __('login.error.invalid_credentials');
        }
    }
}

$currentLang = getCurrentLang();
?>
<!DOCTYPE html>
<html lang="<?= sanitize($currentLang) ?>">
<head>
    <meta charset="UTF-8">
    <?php renderPwaHead(); ?>
    <title><?= __('login.title') ?> - <?= APP_SHORT_NAME ?></title>
    <?php renderVendorStyles(); ?>
</head>
<body class="login-page">
    <div class="login-language-bar">
        <?php foreach (SUPPORTED_LANGUAGES as $lang): ?>
            <a href="<?= languageUrl($lang) ?>"
               class="login-lang-link<?= $currentLang === $lang ? ' active' : '' ?>">
                <?= sanitize(__('lang.' . $lang)) ?>
            </a>
        <?php endforeach; ?>
    </div>
    <div class="login-container">
        <div class="login-card">
            <div class="text-center mb-4">
                <div class="login-logo"><i class="bi bi-whistle"></i></div>
                <h3 class="fw-bold">Ilala Referees</h3>
                <p class="text-muted"><?= __('app.subtitle') ?></p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= sanitize($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <?= csrfField() ?>
                <div class="mb-3">
                    <label class="form-label"><?= __('login.username') ?></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="username" class="form-control" required autofocus
                               value="<?= sanitize($_POST['username'] ?? '') ?>">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label"><?= __('login.password') ?></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 btn-lg">
                    <i class="bi bi-box-arrow-in-right me-2"></i><?= __('login.sign_in') ?>
                </button>
            </form>

            <div class="text-center mt-4">
                <small class="text-muted">
                    <?= __('login.referee_prompt') ?> <a href="<?= APP_URL ?>/register.php"><?= __('login.register_link') ?></a>
                </small>
            </div>
        </div>
    </div>
    <?php renderVendorScripts(); ?>
    <?php renderPwaScripts(); ?>
</body>
</html>
