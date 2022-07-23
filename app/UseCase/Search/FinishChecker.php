<?php

namespace App\UseCase\Search;

use App\Jobs\FinishSearch;
use App\Models\SearchRequest;

class FinishChecker
{
    public function sendFinishMessage(SearchRequest $searchRequest): void
    {
        $searchSources = config('search_sources');

        $isFinished = true;
        foreach ($searchSources as $source) {
            if ($searchRequest->{$source} == '0') {
                $isFinished = false;
                break;
            }
        }

        if ($isFinished) {
            FinishSearch::dispatch($searchRequest)->onQueue('finish_search');
        }
    }
}
