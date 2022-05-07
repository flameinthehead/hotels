<?php

namespace App\Listeners;

use App\Models\City;
use App\Models\TelegramRequest;
use Illuminate\Events\Dispatcher;
use Symfony\Component\Workflow\TransitionBlocker;
use ZeroDaHero\LaravelWorkflow\Events\GuardEvent;

class TelegramRequestWorkflowSubscriber
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
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

        $telegramRequest->city()->associate($city);
    }
}
