<?php

namespace App\UseCase\Search;

use App\Models\Proxy;
use App\Models\SearchRequest;
use App\Models\TelegramRequest;

class Search
{
    private ?string $source = null;

    private ?Proxy $lastProxy = null;

    public function searchByParams(Params $params, TelegramRequest $telegramRequest): void
    {
        $searchRequest = new SearchRequest();
        $searchRequest->hash = $params->getHash($telegramRequest->telegram_from_id);
        $searchRequest->telegram_request_id = $telegramRequest->id;
        $searchRequest->save();

        $sources = config('search_sources');

        foreach ($sources as $source) {
            $jobClassName = 'App\Jobs\Search'.ucfirst($source);
            if (!class_exists($jobClassName)) {
               throw new \Exception('Не найден job для источника ' . $source);
            }
            $jobClassName::dispatch($params, $searchRequest->hash)->onQueue('search_' . $source);
        }
    }

    public function getLastProxy(): ?Proxy
    {
        return $this->lastProxy;
    }

    public function getLastSource(): ?string
    {
        return $this->source;
    }

    public function disableLastProxy(): void
    {
        $proxy = $this->getLastProxy();
        $source = $this->getLastSource();

        if (!empty($proxy) && !empty($source)) {
            $proxy->{$source} = '0';
            $proxy->save();
        }
    }
}
