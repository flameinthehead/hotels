<?php

namespace App\UseCase\Search;

use App\Exceptions\SearchFactoryException;

class SearchFactory
{

    public static function makeSearchBySourceName(string $sourceName): SearchSourceInterface
    {
        $searchSources = config('search_sources');
        if (!in_array($sourceName, $searchSources)) {
            throw new SearchFactoryException('Не найден поисковой источник с именем '. $sourceName);
        }

        return \App::get($sourceName);
    }
}
