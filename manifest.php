<?php
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/manifest+json; charset=utf-8');

echo json_encode([
    'name' => APP_NAME,
    'short_name' => APP_SHORT_NAME,
    'description' => 'Mobile referee management for Ilala district',
    'start_url' => APP_URL . '/login.php',
    'scope' => APP_URL . '/',
    'display' => 'standalone',
    'orientation' => 'portrait-primary',
    'background_color' => '#1a5276',
    'theme_color' => '#1a5276',
    'lang' => getCurrentLang(),
    'icons' => [
        [
            'src' => assetUrl('icons/icon.svg'),
            'sizes' => 'any',
            'type' => 'image/svg+xml',
            'purpose' => 'any',
        ],
        [
            'src' => assetUrl('icons/icon.svg'),
            'sizes' => '512x512',
            'type' => 'image/svg+xml',
            'purpose' => 'maskable',
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
