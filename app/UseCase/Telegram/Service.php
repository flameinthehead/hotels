<?php

namespace App\UseCase\Telegram;

use App\Models\TelegramRequest;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Workflow\Workflow;
use ZeroDaHero\LaravelWorkflow\Facades\WorkflowFacade;

class Service
{
    public const CHOOSE_CITY_MESSAGE = 'Введите название города';
    public const CHOOSE_CHECK_IN_DATE = 'Выберите дату заселения';
    public const CHOOSE_CHECK_OUT_DATE = 'Выберите дату отъезда';
    public const CHOOSE_ADULTS_COUNT = 'Введите количество взрослых';

    public const CITY_ID = 'city_id';
    public const CHECK_IN = 'check_in';
    public const CHECK_OUT = 'check_out';
    public const ADULTS = 'adults';

    public const MESSAGE_SEQUENCE = [
        self::CITY_ID,
        self::CHECK_IN,
        self::CHECK_OUT,
        self::ADULTS,
    ];

    public const MESSAGE_BY_COLUMN_LIST = [
        self::CITY_ID => self::CHOOSE_CITY_MESSAGE,
        self::CHECK_IN => self::CHOOSE_CHECK_IN_DATE,
        self::CHECK_OUT => self::CHOOSE_CHECK_OUT_DATE,
        self::ADULTS => self::CHOOSE_ADULTS_COUNT,
    ];

    public function __construct(private TelegramRequest $entity, private Sender $sender)
    {
    }

    public function processRequest(int $fromId, string $message)
    {
        $notFinishedTgRequest = $this->entity->findNotFinishedByUserId($fromId);

        if (empty($notFinishedTgRequest)) {
            TelegramRequest::create([
                'status' => TelegramRequest::STATUS_NEW,
                'telegram_from_id' => $fromId,
            ]);
            return $this->sender->sendMessage($fromId, self::CHOOSE_CITY_MESSAGE);
        }

        $workflow = WorkflowFacade::get($notFinishedTgRequest);

        $workflow->apply($notFinishedTgRequest, 'choose_city');
        Log::debug($notFinishedTgRequest);
        $notFinishedTgRequest->save();
    }

    /*private function getActualStep(TelegramRequest $telegramRequest): string
    {
        foreach (self::MESSAGE_SEQUENCE as $columnName) {
            if (empty($telegramRequest->{$columnName})) {
                return $columnName;
            }
        }

        throw new \Exception('Не удалось получить текущий шаг');
    }

    private function getActualMessage(TelegramRequest $telegramRequest): string
    {
        foreach (self::MESSAGE_SEQUENCE as $columnName) {
            if (empty($telegramRequest->{$columnName}) && isset(self::MESSAGE_BY_COLUMN_LIST[$columnName])) {
                return self::MESSAGE_BY_COLUMN_LIST[$columnName];
            }
        }

        throw new \Exception('Не удалось получить сообщение для отправки');
    }*/
}
