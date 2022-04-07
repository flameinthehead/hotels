<?php

namespace App\UseCase\Search;

interface SearchResultFactory
{
    public static function makeResult(array $searchResult, SearchParamsFactoryInterface $params): Result;
}
