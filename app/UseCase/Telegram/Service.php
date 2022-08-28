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
    public const MESSAGE_CHOOSE_CITY = 'Введите название города';
    public const MESSAGE_NEW_SEARCH = 'Новый поиск?';
    public const MESSAGE_NEW_SEARCH_BUTTON = 'Начать';

    public function __construct(
        private TelegramRequest $entity,
        private Sender $sender,
        private Calendar $calendar,
        private Formatter $formatter,
        private Stars $stars
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
        foreach ($messages as $photoUrl => $message) {
            if (mb_strpos($photoUrl, Formatter::NO_PHOTO) !== false) {
                $this->sender->sendMessage($chatId, $message);
            } else {
                if (mb_strpos($photoUrl, '.webp') !== false) {
                    $this->sender->sendSticker($chatId, $photoUrl);
                    $this->sender->sendMessage($chatId, $message);
                } else {
                    $this->sender->sendPhoto($chatId, $photoUrl, $message);
                }
            }

            ++$count;
            if ($count == 10) { // @TODO пагинация
                break;
            }
        }

        $this->sender->sendMessage(
            $chatId,
            self::MESSAGE_NEW_SEARCH,
            [
                [
                    ['text' => self::MESSAGE_NEW_SEARCH_BUTTON, 'callback_data' => '/new_search']
                ]
            ]
        );
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
            $this->sender->sendMessage($fromId, self::MESSAGE_CHOOSE_CITY);
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

        if (!empty($callBackMessageId)) {
            if ($this->calendar->isUselessCallBackData($callBackData)) {
               return;
            }

            if ($this->calendar->isSelectedDate($callBackData)) {
                $selectedDate = $this->calendar->parseDate($callBackData);
                $this->sender->editMessage(
                    $fromId,
                    $callBackMessageId,
                    sprintf(
                        '%s: <b>%s</b>',
                        $transitionMetadata['selected_date_message'],
                        $selectedDate->format('d.m.Y')
                    )
                );
            } else {
                $this->sender->editMessage(
                    $fromId,
                    $callBackMessageId,
                    $prevMessage,
                    $this->calendar->makeCalendar($callBackData)
                );
                return;
            }
        }


        if (isset($transitionMetadata['need_calendar']) && $transitionMetadata['need_calendar'] === true) {
            $this->sender->sendMessage(
                $fromId,
                $transitionMetadata['next_message'],
                $this->calendar->makeCalendar($callBackData)
            );
        } elseif(isset($transitionMetadata['need_stars']) && $transitionMetadata['need_stars'] === true) {
            $this->sender->sendMessage(
                $fromId,
                $transitionMetadata['next_message'],
                $this->stars->makeButtons(),
            );
        } else {
            $this->sender->sendMessage($fromId, $transitionMetadata['next_message']);
        }
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
