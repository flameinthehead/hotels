<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::any(
    '/webhook/TGGkrT7YO34oh4D9beSMzYSO6c',
    [\App\Http\Controllers\Api\TelegramController::class, 'messageHandler']
);


Route::get('/search', [\App\Http\Controllers\Api\SearchController::class, 'search'])->name('search');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


