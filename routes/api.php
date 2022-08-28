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

Route::post(
    '/webhook/TGGkrT7YO34oh4D9beSMzYSO6c',
    [\App\Http\Controllers\Api\TelegramController::class, 'messageHandler']
);


Route::get('/search', [\App\Http\Controllers\Api\SearchController::class, 'search'])->name('search');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/test', function (\App\UseCase\Telegram\Sender $sender){
    $url = 'https://i.sutochno.ru/lxG6h3lE0ixJKU96JCDyP1A9yx4r1mYQuJ4ea97agaY/fit/400/300/no/1/czM6Ly9zdGF0aWMuc3V0b2Nobm8ucnUvZG9jL2ZpbGVzL29iamVjdHMvMC85MzQvNjMxLzYyMjVlMzZjMDRmMWEuanBlZw.webp';
    $headers = get_headers($url);
    dd($headers);
//    mime_content_type($url);
   /* $photoUrl = 'https://i.sutochno.ru/_9uFmQv8WKCN4IsUNxraYlG8Ja2JN-PAPanqJkunSKA/fit/400/300/no/1/czM6Ly9zdGF0aWMuc3V0b2Nobm8ucnUvZG9jL2ZpbGVzL29iamVjdHMvMS8yOS81MS82MjYyYWE0ZjAxMzU1LkpQRw.webp';
    $post = array('chat_id' => 216714025,'document'=>new CurlFile($photoUrl), 'caption' => 'fsdfgfgdf');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,"https://api.telegram.org/bot" . '5447540371:AAFwINLxVaDBWk5x1aELBX36hWPmldrMgR4' . "/sendDocument");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_exec ($ch);
    curl_close ($ch)*/;
//    $sender->sendPhoto('216714025', $photoUrl, 'test');
});


