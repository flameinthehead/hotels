<?php

namespace App\Jobs;

use App\Models\Proxy;
use App\UseCase\Search\Params;
use App\UseCase\Yandex\Search;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SearchYandex implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private Params $params)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Search $searchService, Proxy $proxy)
    {
        try {
            $searchService->setParams($this->params);
            $searchService->search($proxy->getAllEnabledBySource('yandex'));
        } catch (\Throwable $e) {
            Log::error($e->getMessage(), $e->getTrace());
        }
    }
}
