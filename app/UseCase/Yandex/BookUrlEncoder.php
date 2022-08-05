<?php

namespace App\UseCase\Yandex;

use App\UseCase\Search\BookUrlEncoderInterface;

class BookUrlEncoder implements BookUrlEncoderInterface
{
    public const PARTNER_LINK_PATTERN = 'https://tp.media/r?marker=193372&trs=179356&p=5916&u={book_url}&campaign_id=193';

    public function encode(string $bookLink): string
    {
        return str_replace('{book_url}', urlencode($bookLink), self::PARTNER_LINK_PATTERN);
    }
}
