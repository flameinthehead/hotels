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
        $fromId = $request->input('message.from.id');
        $message = $request->input('message.text');
        try {
            $this->telegramService->processRequest($fromId, $message);
        } catch (\Throwable $e) {
            Log::error('Ошибка при обработке ответа от ТГ '.$e->getMessage());
        }
    }
}
