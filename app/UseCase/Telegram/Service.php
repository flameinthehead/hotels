<?php

namespace App\UseCase\Telegram;

use App\Models\TelegramRequest;
use Illuminate\Support\Facades\Log;
use ZeroDaHero\LaravelWorkflow\Facades\WorkflowFacade;

class Service
{
    public const CHOOSE_CITY_MESSAGE = 'Введите название города';

    public function __construct(private TelegramRequest $entity, private Sender $sender, private Calendar $calendar)
    {
    }

    public function processRequest(int $fromId, string $message, string $callBackData = '', int $callBackMessageId = null): bool
    {
        /** @var TelegramRequest */
        $notFinishedTgRequest = $this->entity->findNotFinishedByUserId($fromId);

        if (empty($notFinishedTgRequest)) {
            TelegramRequest::create([
                'status' => TelegramRequest::STATUS_NEW,
                'telegram_from_id' => $fromId,
                'last_message' => $message,
            ]);
            $this->sender->sendMessage($fromId, self::CHOOSE_CITY_MESSAGE);
            return true;
        }

        if (!empty($callBackData)) {
            $notFinishedTgRequest->setLastMessage($callBackData);
        } else {
            $notFinishedTgRequest->setLastMessage($message);
        }

        $notFinishedTgRequest->save();

        /** @var \Symfony\Component\Workflow\Workflow $workflow */
        $workflow = WorkflowFacade::get($notFinishedTgRequest);

        foreach($workflow->getDefinition()->getTransitions() as $transition) {
            if(
                in_array($notFinishedTgRequest->status, $transition->getFroms())
                && !$workflow->can($notFinishedTgRequest, $transition->getName())
                && $transitionBlockerList = $workflow->buildTransitionBlockerList($notFinishedTgRequest, $transition->getName())
            ){
                /** @var \Symfony\Component\Workflow\TransitionBlocker $blocker */
                foreach ($transitionBlockerList as $blocker) {
                    $this->sender->sendMessage($fromId, $blocker->getMessage());
                    return false;
                }
            }

            if ($workflow->can($notFinishedTgRequest, $transition->getName())) {
                $workflow->apply($notFinishedTgRequest, $transition->getName());
                break;
            }
        }

        $notFinishedTgRequest->save();


        if (empty($transition)) {
            throw new \Exception('Ошибка при отправке следующего сообщения');
        }
        $transitionMetadata = $workflow->getMetadataStore()->getTransitionMetadata($transition);
        if (empty($transitionMetadata) || !isset($transitionMetadata['next_message'])) {
            throw new \Exception('Не задано сообщение для отправки в ТГ');
        }
        if(!empty($transitionMetadata['needCalendar'])){
            Log::debug('Callback data: '.$callBackData);
            Log::debug('Callback message id: '.$callBackMessageId);

            if (!empty($callBackMessageId)) {
                $this->sender->editMessage(
                    $fromId,
                    $callBackMessageId,
                    $transitionMetadata['next_message'],
                    $this->calendar->makeCalendar($callBackData)
                );
            } else {
                $this->sender->sendMessage(
                    $fromId,
                    $transitionMetadata['next_message'],
                    $this->calendar->makeCalendar($callBackData)
                );
            }
        } else {
            $this->sender->sendMessage($fromId, $transitionMetadata['next_message']);
        }


        return true;
    }
}
