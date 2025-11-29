<?php
declare(strict_types=1);

// Absolute path untuk production
require_once '/home/user/srp/src/Config/bootstrap.php';

use SRP\Controllers\AuthController;

AuthController::login();
