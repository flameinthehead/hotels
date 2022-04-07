<?php

namespace App\UseCase\Search;


use App\Models\Proxy;
use Illuminate\Support\Collection;

interface SearchSourceInterface
{
    public function search(Proxy $proxy): Collection;

    public function setParams(Params $generalParams);
}
