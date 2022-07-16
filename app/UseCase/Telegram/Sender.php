<?php

namespace App\UseCase\Telegram;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;

class Sender
{
    public const BASE_URL = 'https://api.telegram.org/bot';
    public const DISABLE_WEB_PAGE_PREVIEW = 'disable_web_page_preview';
    public const IS_DISABLED_LINK_PREVIEW = true;
    public const PARSE_MODE_FIELD = 'parse_mode';
    public const PARSE_MODE_VALUE = 'html';

    public function __construct(private Client $client)
    {
    }

    public function sendMessage(int $chatId, string $message, array $buttons = []): void
    {
        $request = [
            'chat_id' => $chatId,
            'text' => $message,
        ];

        if (!empty($buttons)) {
            $request['reply_markup'] = json_encode(['inline_keyboard' => $buttons]);
        }

        $this->makeRequest('sendMessage', $request);
    }

    public function editMessage(int $chatId, int $messageId, string $newMessage, array $buttons = [])
    {
        $request = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $newMessage,
        ];

        if (!empty($buttons)) {
            $request['reply_markup'] = json_encode(['inline_keyboard' => $buttons]);
        }

        $this->makeRequest('editMessageText', $request);
    }

    public function sendPhoto(int $chatId, string $photoUrl, string $caption): void
    {
        $request = [
            'chat_id' => $chatId,
            'photo' => $photoUrl,
            'caption' => $caption,
        ];

        $this->makeRequest('sendPhoto', $request);
    }

    private function makeRequest(string $method, array $params = []): bool
    {
        $url = sprintf(self::BASE_URL.'%s/%s', env('TELEGRAM_BOT_TOKEN'), $method);

        Log::debug('Send request '.json_encode($params, JSON_UNESCAPED_UNICODE));

        $params[self::DISABLE_WEB_PAGE_PREVIEW] = self::IS_DISABLED_LINK_PREVIEW;
        $params[self::PARSE_MODE_FIELD] = self::PARSE_MODE_VALUE;

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
