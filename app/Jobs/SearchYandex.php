<?php

namespace App\Jobs;

use App\Models\Proxy;
use App\Models\SearchRequest;
use App\UseCase\Search\FinishChecker;
use App\UseCase\Search\Params;
use App\UseCase\Yandex\Search;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SearchYandex implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Params $params, private string $hash)
    {
    }

    public function handle(Search $searchService, Proxy $proxy, FinishChecker $finishChecker): void
    {
        $searchRequest = SearchRequest::where('hash', $this->hash)->firstOrFail();
        try {
            $searchService->setParams($this->params);
            $searchService->search($proxy->getAllEnabledBySource('yandex'), $searchRequest);
        } catch (\Throwable $e) {
            Log::error($e->getMessage(), $e->getTrace());
        } finally {
            try {
                // нужно заново поискать
                $searchRequest = SearchRequest::where('hash', $this->hash)->firstOrFail();
                $searchRequest->yandex = '1';
                $searchRequest->save();

                $finishChecker->sendFinishMessage($searchRequest);
            } catch (\Throwable $e) {
                Log::error($e->getMessage(), $e->getTrace());
            }
        }
    }
}
