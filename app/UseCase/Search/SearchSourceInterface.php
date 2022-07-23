<?php

namespace App\UseCase\Search;

use App\Models\SearchRequest;

interface SearchSourceInterface
{
    public function search(array $proxyList, SearchRequest $searchRequest): void;

    public function setParams(Params $generalParams);

    public function getUrl(): string;

    public function getOptions(): array;

    public function isValidResponse(array $content): bool;
}
