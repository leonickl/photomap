<?php

use App\Controllers\MapController;
use PXP\Router\Route;
use App\Controllers\AssetController;
use App\Controllers\ImageController;

Route::get('/')->do(MapController::class, 'index');
Route::get('/create')->do(MapController::class, 'create');
Route::post('/')->do(MapController::class, 'store');

Route::get('/css/{file}')->do(AssetController::class, 'css');
Route::get('/img/{file}')->do(ImageController::class, 'img');
