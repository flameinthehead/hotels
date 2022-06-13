<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TelegramRequest;
use App\UseCase\Telegram\Service;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{


    public function __construct(private Service $telegramService)
    {
    }

    public function messageHandler(TelegramRequest $request)
    {
        $callBackQueryPrefix = '';
        $callBackData = '';
        if ($request->has('callback_query')) {
            $callBackQueryPrefix = 'callback_query.';
            $callBackData = $request->input($callBackQueryPrefix.'data');
        }
        $fromId = $request->input($callBackQueryPrefix.'message.chat.id');
        $message = $request->input($callBackQueryPrefix.'message.text');

        try {
            $this->telegramService->processRequest($fromId, $message, $callBackData);
        } catch (\Throwable $e) {
            Log::error('Ошибка при обработке ответа от ТГ '.$e->getMessage());
        }
    }
}
