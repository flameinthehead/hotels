<?php

namespace App\UseCase\Yandex;

use App\UseCase\Search\BookUrlEncoderInterface;

class BookUrlEncoder implements BookUrlEncoderInterface
{
    public function encode(string $bookLink): string
    {
        return $bookLink;
    }
}
