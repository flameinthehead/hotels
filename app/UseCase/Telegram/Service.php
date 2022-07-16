<?php

namespace App\UseCase\Telegram;

use App\Models\TelegramRequest;
use App\UseCase\Search\Params;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use ZeroDaHero\LaravelWorkflow\Facades\WorkflowFacade;

class Service
{
    public const CHOOSE_CITY_MESSAGE = 'Введите название города';

    public function __construct(private TelegramRequest $entity, private Sender $sender, private Calendar $calendar)
    {
    }

    public function processRequest(
        int $fromId,
        string $message,
        string $callBackData = '',
        int $callBackMessageId = null
    ): Params|bool {
        $notFinishedTgRequest = $this->findTgRequest($fromId, $message);
        if (!$notFinishedTgRequest) {
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

        if (!$transition = $this->processWorkflow($workflow, $notFinishedTgRequest, $fromId, $callBackData)) {
            return false;
        }

        $transitionMetadata = $workflow->getMetadataStore()->getTransitionMetadata($transition);
        if (isset($transitionMetadata['is_final_message']) && $transitionMetadata['is_final_message'] === true) {
            return $this->getSearchParamsByTelegramRequest($notFinishedTgRequest);
        }

        $this->sendFinalMessage($transitionMetadata, $fromId, $message, $callBackData, $callBackMessageId);

        return true;
    }

    private function findTgRequest(int $fromId, string $message): ?TelegramRequest
    {
        $notFinishedTgRequest = $this->entity->findNotFinishedByUserId($fromId);

        if (empty($notFinishedTgRequest)) {
            TelegramRequest::create([
                'status' => TelegramRequest::STATUS_NEW,
                'telegram_from_id' => $fromId,
                'last_message' => $message,
            ]);
            $this->sender->sendMessage($fromId, self::CHOOSE_CITY_MESSAGE);
        }

        return $notFinishedTgRequest;
    }

    private function processWorkflow(
        Workflow $workflow,
        TelegramRequest $notFinishedTgRequest,
        int $fromId,
        string $callBackData = ''
    ): ?Transition
    {
        $transition = null;
        foreach($workflow->getDefinition()->getTransitions() as $transition) {
            if(
                in_array($notFinishedTgRequest->getStatus(), $transition->getFroms())
                && !$workflow->can($notFinishedTgRequest, $transition->getName())
                && $transitionBlockerList = $workflow->buildTransitionBlockerList($notFinishedTgRequest, $transition->getName())
            ){
                if (!empty($callBackData) && !$this->calendar->isSelectedDate($callBackData)) {
                    Log::debug('Transition: '.var_export($transition, true));
                    return $transition;
                }

                /** @var \Symfony\Component\Workflow\TransitionBlocker $blocker */
                foreach ($transitionBlockerList as $blocker) {
                    $this->sender->sendMessage($fromId, $blocker->getMessage());
                    return null;
                }
            }

            if ($workflow->can($notFinishedTgRequest, $transition->getName())) {
                Log::debug('Transition name: '.$transition->getName());
                $workflow->apply($notFinishedTgRequest, $transition->getName());
                break;
            }
        }

        $notFinishedTgRequest->save();
        return $transition;
    }

    private function sendFinalMessage(
        array $transitionMetadata,
        int $fromId,
        string $prevMessage,
        string $callBackData = '',
        int $callBackMessageId = null
    ): void
    {
        Log::debug('Transition metadata: '.var_export($transitionMetadata, true));


        if (empty($transitionMetadata) || !isset($transitionMetadata['next_message'])) {
            throw new \Exception('Не задано сообщение для отправки в ТГ');
        }

        if (!empty($callBackMessageId) && !$this->calendar->isSelectedDate($callBackData)) {
            $this->sender->editMessage(
                $fromId,
                $callBackMessageId,
                $prevMessage,
                $this->calendar->makeCalendar($callBackData)
            );
            return;
        }


        if (empty($transitionMetadata['needCalendar'])) {
            $this->sender->sendMessage($fromId, $transitionMetadata['next_message']);
            return;
        }

        $this->sender->sendMessage(
            $fromId,
            $transitionMetadata['next_message'],
            $this->calendar->makeCalendar($callBackData)
        );
    }

    private function getSearchParamsByTelegramRequest(TelegramRequest $tgRequest): Params
    {
        $params = new Params();
        $params->setCity($tgRequest->getCity());
        $params->setCheckInDate(Carbon::make($tgRequest->getCheckInDate()->format('Y-m-d H:i:s')));
        $params->setCheckOutDate(Carbon::make($tgRequest->getCheckOutDate()->format('Y-m-d H:i:s')));
        $params->setAdults($tgRequest->getAdults());

        return $params;
    }
}
