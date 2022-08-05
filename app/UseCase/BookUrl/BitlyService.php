<?php

namespace App\UseCase\BookUrl;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class BitlyService
{
    private const BASE_URL = 'https://api-ssl.bitly.com/v4/shorten';
    private const DOMAIN = 'bit.ly';
    private string $token;

    public function __construct(private Client $client)
    {
        $this->token = env('BITLY_TOKEN');
    }

    public function short(string $url): string
    {
        $result = $this->client->post(
            self::BASE_URL,
            [
                RequestOptions::JSON => [
                    'group_guid' => 'ostrovok',
                    'domain' => self::DOMAIN,
                    'long_url' => $url,
                ],
                RequestOptions::HEADERS => [
                    'Authorization: Bearer ' . $this->token
                ]
            ]
        );

        if ($result->getStatusCode() !== 200) {
            throw new \Exception('Bitly url generation error');
        }

        return $result->getBody()->getContents();
    }
}
