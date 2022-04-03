<?php

namespace App\UseCase\Proxy\Source;

use App\UseCase\Proxy\SourceInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;

class RootJazz implements SourceInterface
{
    public const SOURCE = 'rootjazz';
    public const BASE_URL = 'https://rootjazz.com/proxies/proxies.txt';

    public function __construct(private Client $client)
    {
    }

    public function parse(): Collection
    {
        $result = $this->client->request('GET', self::BASE_URL);
        if ($result->getStatusCode() !== 200) {
            throw new \Exception($this->getSource().' Код ответа != 200');
        }
        $content = $result->getBody()->getContents();
        $ipList = array_filter(explode("\n", $content));
        if (empty($ipList)) {
            throw new \Exception($this->getSource().' Не удалось превратить ответ в массив');
        }
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
