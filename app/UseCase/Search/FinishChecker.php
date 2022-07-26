<?php

namespace App\UseCase\Search;

use App\Jobs\FinishSearch;
use App\Models\SearchRequest;

class FinishChecker
{
    public function sendFinishMessage(SearchRequest $searchRequest): void
    {
        if ($searchRequest->is_finished == '1') {
            return;
        }
        $searchSources = config('search_sources');

        $isFinished = true;
        foreach ($searchSources as $source) {
            if ($searchRequest->{$source} == '0') {
                $isFinished = false;
                break;
            }
        }

        if ($isFinished) {
            $searchRequest->is_finished = '1';
            $searchRequest->save();
            FinishSearch::dispatch($searchRequest)->onQueue('finish_search');
        }
    }
}
