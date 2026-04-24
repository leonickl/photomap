<?php

use App\Controllers\AssetController;
use App\Controllers\ImageController;
use App\Controllers\MapController;
use PXP\Router\Route;

Route::get('/')->do(MapController::class, 'index');

Route::get('/create')->do(MapController::class, 'upload');
Route::post('/')->do(MapController::class, 'storeFromUpload');

Route::get('/camera')->do(MapController::class, 'camera');
Route::post('/camera')->do(MapController::class, 'storeFromCamera');

Route::get('/css/{file}')->do(AssetController::class, 'css');
Route::get('/img/{file}')->do(ImageController::class, 'img');
