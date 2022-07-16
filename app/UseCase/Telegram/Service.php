<?php

namespace App\UseCase\Telegram;

use App\Models\TelegramRequest;
use App\UseCase\Search\Params;
use Illuminate\Support\Carbon;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use ZeroDaHero\LaravelWorkflow\Facades\WorkflowFacade;

class Service
{
    public const CHOOSE_CITY_MESSAGE = 'Введите название города';

    public function __construct(
        private TelegramRequest $entity,
        private Sender $sender,
        private Calendar $calendar,
        private Formatter $formatter
    ) {
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

    public function sendResults(int $chatId, array $results): void
    {
        $messages = $this->formatter->formatSearchResults($results);
        if (empty($messages)) {
            return;
        }

        $count = 0;
        foreach ($messages as $message) {
            $this->sender->sendMessage($chatId, $message);
            ++$count;
            if ($count == 10) { // @TODO пагинация
                break;
            }
        }
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
                    return $transition;
                }

                /** @var \Symfony\Component\Workflow\TransitionBlocker $blocker */
                foreach ($transitionBlockerList as $blocker) {
                    $this->sender->sendMessage($fromId, $blocker->getMessage());
                    return null;
                }
            }

            if ($workflow->can($notFinishedTgRequest, $transition->getName())) {
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
        $params->setCheckInDate(Carbon::make($tgRequest->getCheckInDate()));
        $params->setCheckOutDate(Carbon::make($tgRequest->getCheckOutDate()));
        $params->setAdults($tgRequest->getAdults());

        return $params;
    }
}
