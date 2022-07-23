<?php

namespace App\UseCase\Search;


use App\Models\Proxy;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;

interface SearchSourceInterface
{
    public function search(array $proxyList): Collection;

    public function setParams(Params $generalParams);

    public function getUrl(): string;

    public function getOptions(): array;

    public function isValidResponse(array $content): bool;
}
