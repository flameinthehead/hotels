<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class TelegramRequestWorkflowSubscriber
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
            'workflow.telegram_request.guard',
            [\App\Listeners\TelegramRequestWorkflowSubscriber::class, 'onGuard'],
        );
    }

    public function onGuard()
    {
        Log::debug('Hi from onGuard!');
    }
}
