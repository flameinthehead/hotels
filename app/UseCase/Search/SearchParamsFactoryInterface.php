<?php

namespace App\UseCase\Search;

interface SearchParamsFactoryInterface
{
    public static function makeSourceParams(Params $generalParams);
}
