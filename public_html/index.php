<?php
declare(strict_types=1);

// Absolute path untuk production
require_once dirname(__DIR__) . '/srp/src/bootstrap.php';

use SRP\Controllers\DashboardController;

DashboardController::index();
