<?php

namespace App\UseCase\Proxy\Source;

use App\UseCase\Proxy\SourceInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use phpDocumentor\Reflection\DocBlock\Serializer;

class Geonode implements SourceInterface
{
    public const SOURCE = 'geonode';
    public const BASE_URL = 'https://proxylist.geonode.com/api/proxy-list?limit=500&page=1&sort_by=lastChecked&sort_type=desc';

    public function __construct(private Client $client)
    {
    }

    public function parse(): Collection
    {
        $output = [];
        $response = $this->client->request('GET', self::BASE_URL);
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Код ответа '.$this->getSource().' != 200');
        }

        $content = $response->getBody()->getContents();
        if (empty($content)) {
            throw new \Exception($this->getSource().' пустой контент');
        }

        $json = json_decode($content, true);

        if (empty($json) || !is_array($json) || !isset($json['data']) || empty($json['data'])) {
            throw new \Exception($this->getSource().' некорректный ответ');
        }

        foreach ($json['data'] as $item) {
            if (!empty($item['ip']) && !empty($item['port'])) {
                $output[] = [
                    'address' => $item['ip'].':'.$item['port'],
                    'source' => $this->getSource(),
                ];
            }
        }

        return collect($output);
    }

    public function getSource(): string
    {
        return self::SOURCE;
    }

}
