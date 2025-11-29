<?php
declare(strict_types=1);

// Portable path untuk shared hosting: dirname(__DIR__) = /home/username
require_once dirname(__DIR__) . '/srp/src/bootstrap.php';

use SRP\Controllers\DashboardApiController;

// Dashboard data endpoint - provides combined settings + logs data
DashboardApiController::handle();
