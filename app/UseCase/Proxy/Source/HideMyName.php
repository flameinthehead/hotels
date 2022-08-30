<?php

namespace App\UseCase\Proxy\Source;

use App\Models\Proxy;
use App\UseCase\Proxy\SourceInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;

class HideMyName implements SourceInterface
{
    public const BASE_URL = 'https://hidemy.name/ru/proxy-list/';
    public const PAGES = 200;
    public const SOURCE = 'hidemyname';

    public function __construct(private Client $client)
    {
    }

    public function parse(): Collection
    {
        $proxyList = [];
        // первые 20 страниц по пингу
        for($page = 1; $page <= self::PAGES; ++$page) {
            try {
                $url = self::BASE_URL;
                if($page > 1){
                    $url .= '?start='.(($page - 1) * 64);
                }

                $options = [
                    'headers' => [
                        "authority" => "hidemy.name",
                        "method" => "GET",
                        "path" => "/ru/proxy-list/?maxtime=100",
                        "scheme" => "https",
                        "accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
                        "accept-encoding" => "gzip, deflate, br",
                        "accept-language" => "ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
                        "cache-control" => "max-age=0",
                        "referer" => "https://hidemy.name/ru/proxy-list/?start=64",
                        "sec-ch-ua" => '" Not A;Brand";v="99", "Chromium";v="99"',
                        "sec-ch-ua-mobile" => "?0",
                        "sec-ch-ua-platform" => '"Linux"',
                        "sec-fetch-dest" => "document",
                        "sec-fetch-mode" => "navigate",
                        "sec-fetch-site" => "same-origin",
                        "sec-fetch-user" => "?1",
                        "upgrade-insecure-requests" => "1",
                        "user-agent" => "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.74 Safari/537.36",
                    ],
                    'connect_timeout' => 10,
                ];

                if (!empty($proxy)) {
                   $options['proxy'] = $proxy->address;
                }
                $response = $this->client->request('GET', $url, $options);
                if ($response->getStatusCode() !== 200) {
                    throw new \Exception('Код ответа от '.$this->getSource().' != 200');
                }

                $html = $response->getBody()->getContents();
                if (empty($html)) {
                    throw new \Exception('Пустой ответ от '.$this->getSource());
                }

                if(!preg_match_all('/<tr>(.*?)<\/tr>/', $html, $matches)) {
                    throw new \Exception('Невозможно получить прокси '.$this->getSource());
                }

                foreach ($matches[1] as $key => $tr) {
                    if($key == 0){ // пропускаем шапку
                        continue;
                    }

                    preg_match_all('/<td>(.*?)<\/td>/', $tr, $trMatches);
                    if(!empty($trMatches[1][0]) && !empty($trMatches[1][1])){
                        $proxyList[] = [
                            'address' => $trMatches[1][0].':'.$trMatches[1][1],
                            'source' => $this->getSource()
                        ];
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return collect($proxyList);
    }

    public function getSource(): string
    {
        return self::SOURCE;
    }
}
