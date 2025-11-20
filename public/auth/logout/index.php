<?php
require_once __DIR__ . '/../../../src/includes/autoload.php';

use App\Classes\Auth;

$auth = new Auth();
$auth->logout();

header('Location: /auth/login');
exit;
