<?php

namespace App\Listeners;

use App\Models\City;
use App\Models\TelegramRequest;
use App\UseCase\Telegram\Calendar;
use Illuminate\Events\Dispatcher;
use ZeroDaHero\LaravelWorkflow\Events\CompletedEvent;
use Symfony\Component\Workflow\TransitionBlocker;
use ZeroDaHero\LaravelWorkflow\Events\GuardEvent;

class TelegramRequestWorkflowSubscriber
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(private Calendar $calendar)
    {
    }

    /**
     * Handle the event.
     *
     * @param Dispatcher $events
     * @return void
     */
    public function subscribe($events)
    {
        $events->listen(
            'workflow.telegram_request.guard.choose_city',
            [\App\Listeners\TelegramRequestWorkflowSubscriber::class, 'onGuardChooseCity'],
        );

        $events->listen(
            'workflow.telegram_request.completed.choose_city',
            [\App\Listeners\TelegramRequestWorkflowSubscriber::class, 'onCompletedChooseCity'],
        );


        $events->listen(
            'workflow.telegram_request.guard.choose_check_in',
            [\App\Listeners\TelegramRequestWorkflowSubscriber::class, 'onGuardChooseCheckIn'],
        );

        $events->listen(
            'workflow.telegram_request.completed.choose_check_in',
            [\App\Listeners\TelegramRequestWorkflowSubscriber::class, 'onCompletedChooseCheckIn'],
        );

        $events->listen(
            'workflow.telegram_request.guard.choose_check_out',
            [\App\Listeners\TelegramRequestWorkflowSubscriber::class, 'onGuardChooseCheckOut'],
        );

        $events->listen(
            'workflow.telegram_request.completed.choose_check_out',
            [\App\Listeners\TelegramRequestWorkflowSubscriber::class, 'onCompletedChooseCheckOut'],
        );

        $events->listen(
            'workflow.telegram_request.guard.choose_adults',
            [\App\Listeners\TelegramRequestWorkflowSubscriber::class, 'onGuardChooseAdults'],
        );

        $events->listen(
            'workflow.telegram_request.completed.choose_adults',
            [\App\Listeners\TelegramRequestWorkflowSubscriber::class, 'onCompletedChooseAdults'],
        );
    }

    public function onGuardChooseCity(GuardEvent $event): void
    {
        /** @var TelegramRequest $telegramRequest */
        $telegramRequest = $event->getSubject();
        $city = (new City())->findByName($telegramRequest->getLastMessage());

        if (empty($city)) {
            $event->addTransitionBlocker(
                new TransitionBlocker('Город не найден. Попробуйте ввести другой город.', '403')
            );
        }
    }

    public function onCompletedChooseCity(CompletedEvent $event): void
    {
        $telegramRequest = $event->getSubject();
        $city = (new City())->findByName($telegramRequest->getLastMessage());
        $telegramRequest->city()->associate($city);
    }

    public function onGuardChooseCheckIn(GuardEvent $event): void
    {
        /** @var TelegramRequest $telegramRequest */
        $telegramRequest = $event->getSubject();

        if (!$this->calendar->isSelectedDate($telegramRequest->getLastMessage())) {
            $event->setBlocked(true);
        }
    }

    public function onCompletedChooseCheckIn(CompletedEvent $event): void
    {
        /** @var TelegramRequest $telegramRequest */
        $telegramRequest = $event->getSubject();
        $telegramRequest->setCheckInDate($this->calendar->parseDate($telegramRequest->getLastMessage()));
    }

    public function onGuardChooseCheckOut(GuardEvent $event): void
    {
        /** @var TelegramRequest $telegramRequest */
        $telegramRequest = $event->getSubject();

        if (!$this->calendar->isSelectedDate($telegramRequest->getLastMessage())) {
            $event->setBlocked(true);
        }
    }

    public function onCompletedChooseCheckOut(CompletedEvent $event): void
    {
        $telegramRequest = $event->getSubject();
        $telegramRequest->setCheckOutDate($this->calendar->parseDate($telegramRequest->getLastMessage()));
    }

    public function onGuardChooseAdults(GuardEvent $event): void
    {
        $telegramRequest = $event->getSubject();
        if (!is_numeric($telegramRequest->getLastMessage())) {
            $event->addTransitionBlocker(
                new TransitionBlocker('Необходимо ввести число', '403')
            );
        }
    }

    public function onCompletedChooseAdults(CompletedEvent $event): void
    {
        /** @var TelegramRequest $telegramRequest */
        $telegramRequest = $event->getSubject();
        $telegramRequest->setAdults($telegramRequest->getLastMessage());
        $telegramRequest->setIsFinished(true);
    }
}
