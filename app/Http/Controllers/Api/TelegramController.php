<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TelegramRequest;
use App\Models\TelegramRequest as TelegramRequestModel;
use App\UseCase\Search\Params;
use App\UseCase\Search\Search;
use App\UseCase\Telegram\Sender;
use App\UseCase\Telegram\Service;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{


    public function __construct(private Service $telegramService, private Search $searchService, private Sender $sender)
    {
    }

    public function messageHandler(TelegramRequest $request, TelegramRequestModel $entity): void
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
            if (!$fromId) {
                throw new \Exception('Ошибка при определении пользователя');
            }

            if (!$message) {
                throw new \Exception('Допустим только ввод текста');
            }

            $requestParams = $this->telegramService->processRequest($fromId, $message, $callBackData, $callBackMessageId);
            if (!empty($requestParams) && $requestParams instanceof Params) {
                $this->sender->sendMessage($fromId, 'Пожалуйста, подождите, выполняется поиск...');
                $telegramRequestModel = $entity->findNotFinishedByUserId($fromId);
                $this->searchService->searchByParams($requestParams, $telegramRequestModel);
            }
        } catch (\Throwable $e) {
            $this->sender->sendMessage($fromId, 'Произошла ошибка при поиске - '.$e->getMessage().'.');
            Log::error('Ошибка при обработке ответа от ТГ '.$e->getMessage(), $e->getTrace());
            $this->searchService->disableLastProxy();
        }
    }
}
