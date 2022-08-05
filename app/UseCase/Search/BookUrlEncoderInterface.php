<?php

namespace App\UseCase\Search;

interface BookUrlEncoderInterface
{
    public function encode(string $bookLink): string;
}
