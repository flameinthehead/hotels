<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TelegramRequest;
use App\Models\TelegramRequest as TelegramRequestModel;
use App\Models\Result;
use App\UseCase\Search\Params;
use App\UseCase\Search\Search;
use App\UseCase\Telegram\Sender;
use App\UseCase\Telegram\Service;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{


    public function __construct(private Service $telegramService, private Search $searchService, private Sender $sender)
    {
    }

    public function messageHandler(TelegramRequest $request, TelegramRequestModel $entity): void
    {
//        Log::debug($request);

        $callBackQueryPrefix = '';
        $callBackData = '';
        $callBackMessageId = null;
        if ($request->has('callback_query')) {
            $callBackQueryPrefix = 'callback_query.';
            $callBackData = $request->input($callBackQueryPrefix.'data');
            $callBackMessageId = $request->input($callBackQueryPrefix.'message.message_id');
        }
        $fromId = $request->input($callBackQueryPrefix.'message.chat.id');
        $message = $request->input($callBackQueryPrefix.'message.text');

        try {
            $requestParams = $this->telegramService->processRequest($fromId, $message, $callBackData, $callBackMessageId);
            if (!empty($requestParams) && $requestParams instanceof Params) {
                $this->sender->sendMessage($fromId, 'Пожалуйста, подождите, выполняется поиск...');
                $telegramRequestModel = $entity->findNotFinishedByUserId($fromId);
                $this->searchService->searchByParams($requestParams, $telegramRequestModel);
            }
        } catch (\Throwable $e) {
            $this->sender->sendMessage($fromId, 'Произошла ошибка при поиске.');
            Log::error('Ошибка при обработке ответа от ТГ '.$e->getMessage(), $e->getTrace());
            $this->searchService->disableLastProxy();
        }
    }

    public function test()
    {
        $result = new Result();
        $result->setName('kek');
        $result->setPrice(123);

        $result->save();

        return 'OK';


        $url = 'https://travel.yandex.ru/api/hotels/searchHotels?startSearchReason=mapBounds&pageHotelCount=25&pricedHotelLimit=26&totalHotelLimit=50&searchPagePollingId=364e0a9e8461a8d2ada3ade06e80f778-0-newsearch&pollIteration=1&context=364e0a9e8461a8d2ada3ade06e80f778-1-newsearch-0~CiAzNjRlMGE5ZTg0NjFhOGQyYWRhM2FkZTA2ZTgwZjc3OBABGAAgi9bCkPDUpNFyKAgyngYKKwoSbG9uZ3Rlcm1faW50ZXJlc3RzEhVzZWdtZW50LXRyYWluX3RpY2tldHMKLQoSbG9uZ3Rlcm1faW50ZXJlc3RzEhdzZWdtZW50LWludGVyY2l0eV9idXNlcwomChJoZXVyaXN0aWNfc2VnbWVudHMSEHNlZ21lbnQtMGNhMTk2NGQKJgoSaGV1cmlzdGljX3NlZ21lbnRzEhBzZWdtZW50LWMyYzlmYTY3CiwKEmxvbmd0ZXJtX2ludGVyZXN0cxIWc2VnbWVudC1mbGlnaHRfdGlja2V0cwosChJoZXVyaXN0aWNfc2VnbWVudHMSFnNlZ21lbnQtdmlzaXRlZC1ob3RlbHMKFAoLdXNlcl9hZ2VfNnMSBTI1XzM0CiYKEmhldXJpc3RpY19zZWdtZW50cxIQc2VnbWVudC1lOTQwMjA2MQoLCgZnZW5kZXISAW0KJgoSaGV1cmlzdGljX3NlZ21lbnRzEhBzZWdtZW50LWIxY2EyOWFkCjEKE3Nob3J0dGVybV9pbnRlcmVzdHMSGnNlZ21lbnQtaG9seWRheXNfaW5fcnVzc2lhCiYKEmhldXJpc3RpY19zZWdtZW50cxIQc2VnbWVudC1kZmY0NmFlMgoXChFpbmNvbWVfNV9zZWdtZW50cxICQjEKNwoSaGV1cmlzdGljX3NlZ21lbnRzEiFzZWdtZW50LWZhbWlseV9zdGF0dXNfbm90X21hcnJpZWQSBgjZBBDZARIGCNkEEMkBEgYIowQQqQkSBgijBBCiCBIGCNkEEI4BEgYIowQQ0QgSBQifBBACEgYIowQQlwgSBQiuARAAEgYIowQQpwgSBQjaBBAnEgYIowQQmggSBQjmBBABEgYIowQQgAgaCQjmBBAEGPShAhoJCJ8EEAIYmtgxGgkInwQQAxjM0gkaCQiuARAAGJCDPBoJCK4BEAEYr4EBGgcInwQQABgOGggI5gQQABibTxoICJ8EEAEYwUoaCQjmBBABGNe0LBoJCOYEEAIY7csFGgkI5gQQAxjqkggaCQifBBAEGMiBARoICJ8EEAUYwA04AQ&pollEpoch=1&checkinDate=2022-07-30&checkoutDate=2022-07-31&adults=2&geoId=39&navigationToken=0&bbox=39.632782876412136,47.21008763419517~39.82183479078306,47.25470854069878&selectedSortId=relevant-first&geoLocationStatus=unknown';

        $newClient = new \GuzzleHttp\Client(['base_uri' => $url]);

        $proxyList = [
            '172.67.182.63:80',
            '172.67.181.79:80',
            '190.93.244.26:80',
            '203.24.108.101:80',
            '203.28.8.232:80',
            '203.32.121.244:80',
            '91.226.97.8:80',
            '185.171.231.144:80',
            '185.162.231.30:80',
            '185.238.228.133:80',


            '46.53.191.60:3128',
        ];

//        dd(array_chunk($proxyList, 2));

        $requestArr = [];
        foreach($proxyList as $proxy){
            $requestArr[$proxy] = $newClient->getAsync(
                '',
                [
                    'proxy' => $proxy
                ]
            );
        }

        $responses = \GuzzleHttp\Promise\settle($requestArr)->wait();

        $responses = array_filter($responses, function ($item) {
            /** @var Response $response */
            if (!isset($item['value'])) {
                return false;
            }
            $response = $item['value'];
            return $response->getStatusCode() === 200;
        });

        foreach ($responses as $item) {
            $response = $item['value'];
            $content = $response->getBody()->getContents();
            dump($content);
        }
    }
}
