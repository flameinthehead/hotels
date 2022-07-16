<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TelegramRequest;
use App\UseCase\Search\Params;
use App\UseCase\Search\Search;
use App\UseCase\Telegram\Service;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{


    public function __construct(private Service $telegramService, private Search $searchService)
    {
    }

    public function messageHandler(TelegramRequest $request)
    {
        $callBackQueryPrefix = '';
        $callBackData = '';
        $callBackMessageId = null;
        if ($request->has('callback_query')) {
            $callBackQueryPrefix = 'callback_query.';
            $callBackData = $request->input($callBackQueryPrefix.'data');
            $callBackMessageId = $request->input($callBackQueryPrefix.'message.message_id');
        }
        $fromId = $request->input($callBackQueryPrefix.'message.chat.id');
        $message = $request->input($callBackQueryPrefix.'message.text');

        try {
            $requestParams = $this->telegramService->processRequest($fromId, $message, $callBackData, $callBackMessageId);
            if (!empty($requestParams) && $requestParams instanceof Params) {
                $this->searchService->searchByParams($requestParams);
            }
        } catch (\Throwable $e) {
            Log::error('Ошибка при обработке ответа от ТГ '.$e->getMessage());
        }
    }
}
