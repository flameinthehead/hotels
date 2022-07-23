<?php

namespace App\Jobs;

use App\Models\SearchRequest;
use App\UseCase\Search\Sorter;
use App\UseCase\Telegram\Sender;
use App\UseCase\Telegram\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FinishSearch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private SearchRequest $searchRequest)
    {
    }

    public function handle(Sender $sender, Service $telegramService, Sorter $sorter): void
    {
        try {
            $results = $this->searchRequest->searchResults()->get()->all();
            $fromId = $this->searchRequest->telegramRequest->telegram_from_id;
            $sender->sendMessage($fromId, 'Поиск завершён'.(empty($results) ? ' - ничего не найдено' : '').'.');
            if (!empty($results)) {
                $telegramService->sendResults($fromId, $sorter->sort($results));
            }
            $telegramRequest = $this->searchRequest->telegramRequest;
            $telegramRequest->is_finished = '1';
            $telegramRequest->save();
        } catch (\Throwable $e) {
            Log::error($e->getMessage(), $e->getTrace());
        }
    }
}
