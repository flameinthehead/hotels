<?php

namespace App\UseCase\Proxy\Source;

use App\UseCase\Proxy\SourceInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;

class ProxyScrape implements SourceInterface
{
    public const SOURCE = 'proxyscrape';
    public const BASE_URL = 'https://api.proxyscrape.com/v2/?request=getproxies';

    public function __construct(private Client $client)
    {
    }

    public function parse(): Collection
    {
        $response = $this->client->request('GET', self::BASE_URL);
        if ($response->getStatusCode() !== 200) {
            throw new \Exception($this->getSource().' Код ответа != 200');
        }

        $content = $response->getBody()->getContents();
        if (empty($content)) {
            throw new \Exception($this->getSource().' Пустой контент');
        }

        $ipList = array_filter(explode("\r\n", $content));
        $output = [];
        foreach($ipList as $item) {
            $output[] = [
                'address' => $item,
                'source' => $this->getSource(),
            ];
        }

        return collect($output);
    }

    public function getSource(): string
    {
        return self::SOURCE;
    }

}
