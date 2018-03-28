<?php

use FakeLaravel\base\Route;
use FakeLaravel\exceptions\InvalidKeyException;
use FakeLaravel\responses\Base64JsonResponse;
use FakeLaravel\responses\JsonResponse;

Route::get("index.php", function ($request) {
    return new JsonResponse(null);
});
