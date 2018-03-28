<?php
require_once "autoload.php";

use FakeLaravel\base\App;
use FakeLaravel\base\Route;

global $app;

Route::boot();

$appStr = "FakeLaravel\base\App";
$app = new App();

$app->run();
