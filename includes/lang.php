<?php
/**
 * Language / i18n helpers
 */

define('SUPPORTED_LANGUAGES', ['en', 'sw']);
define('DEFAULT_LANGUAGE', 'en');

$translations = [];

function initLanguage(): void
{
    global $translations;

    if (isset($_GET['lang']) && in_array($_GET['lang'], SUPPORTED_LANGUAGES, true)) {
        $_SESSION['lang'] = $_GET['lang'];
    }

    $lang = $_SESSION['lang'] ?? DEFAULT_LANGUAGE;
    if (!in_array($lang, SUPPORTED_LANGUAGES, true)) {
        $lang = DEFAULT_LANGUAGE;
    }

    $_SESSION['lang'] = $lang;

    $langFile = __DIR__ . '/../lang/' . $lang . '.php';
    $translations = file_exists($langFile) ? require $langFile : [];
}

function getCurrentLang(): string
{
    return $_SESSION['lang'] ?? DEFAULT_LANGUAGE;
}

function __(string $key, array $replace = []): string
{
    global $translations;

    $text = $translations[$key] ?? $key;

    foreach ($replace as $search => $value) {
        $text = str_replace(':' . $search, (string) $value, $text);
    }

    return $text;
}

function languageUrl(string $lang): string
{
    $redirect = $_SERVER['REQUEST_URI'] ?? APP_URL . '/dashboard.php';
    $separator = str_contains($redirect, '?') ? '&' : '?';

    return APP_URL . '/set-language.php?lang=' . urlencode($lang) . '&redirect=' . urlencode($redirect);
}
