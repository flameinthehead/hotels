<?php

namespace App\UseCase\Proxy\Source;

use App\UseCase\Proxy\SourceInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;

class HideMyName implements SourceInterface
{
    public const BASE_URL = 'https://hidemy.name/ru/proxy-list/';
    public const PAGES = 100;
    public const SOURCE = 'hidemyname';

    public function __construct(private Client $client)
    {
    }

    public function parse(): Collection
    {
        $proxyList = [];
        // первые 20 страниц по пингу
        for($page = 1; $page <= self::PAGES; ++$page) {
            $url = self::BASE_URL;
            if($page > 1){
                $url .= '?start='.(($page - 1) * 64);
            }
            $response = $this->client->request('GET', $url, [
                'headers' => [
                    "authority" => "hidemy.name",
                    "method" => "GET",
                    "path" => "/ru/proxy-list/?maxtime=100",
                    "scheme" => "https",
                    "accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
                    "accept-encoding" => "gzip, deflate, br",
                    "accept-language" => "ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
                    "cache-control" => "max-age=0",
//                    cookie: t=247711659; PAPVisitorId=40816ca87a3ed15bf5b8DTf2U003aHEH; PAPVisitorId=40816ca87a3ed15bf5b8DTf2U003aHEH; _ym_uid=1647797882863193039; _ym_d=1647797882; _ga=GA1.2.1245665970.1647797882; _gid=GA1.2.1010612292.1647797882; _ym_isad=1
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
            ]);
            $html = $response->getBody()->getContents();
            preg_match_all('/<tr>(.*?)<\/tr>/', $html, $matches);
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
        }

        return collect($proxyList);
    }

    public function getSource(): string
    {
        return self::SOURCE;
    }
}
