<?php

namespace App\UseCase\Telegram;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class Sender
{
    public const BASE_URL = 'https://api.telegram.org/bot';

    public function __construct(private Client $client)
    {
    }

    public function sendMessage(int $chatId, string $message)
    {
        $this->makeRequest('sendMessage', [
            'chat_id' => $chatId,
            'text' => $message,
        ]);
    }

    private function makeRequest(string $method, array $params = [])
    {
        $url = sprintf(self::BASE_URL.'%s/%s', env('TELEGRAM_BOT_TOKEN'), $method);

        $response = $this->client->post($url, [
            RequestOptions::JSON => !empty($params) ? $params : []
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Кот ответа от Telegram != 200');
        }

        $body = json_decode($response->getBody()->getContents(), true);
        if (!isset($body['ok']) || $body['ok'] !== true) {
            throw new \Exception('Некорректный ответ при отправке сообщения через Telegram: метод '.$method);
        }

        return true;
    }
}
