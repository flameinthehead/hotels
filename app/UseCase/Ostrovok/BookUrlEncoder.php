<?php

namespace App\UseCase\Ostrovok;

use App\UseCase\BookUrl\BitlyService;
use App\UseCase\Search\BookUrlEncoderInterface;

class BookUrlEncoder implements BookUrlEncoderInterface
{
    private const PARTNER_LINK_PATTERN = 'https://tp.media/r?marker=193372&trs=179356&p=7038&u={book_url}&campaign_id=459';

    public function __construct(private BitlyService $bitlyService)
    {
    }

    public function encode(string $bookLink): string
    {
        return $this->bitlyService->short(
            str_replace('{book_url}', urlencode($bookLink), self::PARTNER_LINK_PATTERN)
        );
    }
}
