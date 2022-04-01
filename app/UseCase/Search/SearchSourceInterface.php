<?php

namespace App\UseCase\Search;


interface SearchSourceInterface
{
    public function search();

    public function setParams(Params $generalParams);
}
