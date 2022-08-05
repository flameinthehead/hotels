<?php

namespace App\UseCase\Search;

use App\Models\Result;

interface SearchResultFactory
{
    public static function makeResult(
        array $searchResult,
        SearchParamsFactoryInterface $params,
        BookUrlEncoderInterface $bookUrlEncoder
    ): ?Result;
}
