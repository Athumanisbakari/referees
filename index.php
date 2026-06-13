<?php
require_once __DIR__ . '/includes/functions.php';
redirect(isLoggedIn() ? APP_URL . '/dashboard.php' : APP_URL . '/login.php');
