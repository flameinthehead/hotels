<?php

namespace App\UseCase\Proxy\Source;

use App\UseCase\Proxy\SourceInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;

class FreeProxyListNet implements SourceInterface
{
    public const SOURCE = 'freeproxylistnet';
    public const BASE_URL = 'https://free-proxy-list.net/';

    public function __construct(private Client $client, private \DOMDocument $domDocument)
    {
    }

    public function parse(): Collection
    {
        $response = $this->client->request('GET', self::BASE_URL);
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Код ответа '.$this->getSource().' != 200');
        }

        $content = $response->getBody()->getContents();
        if (empty($content)) {
            throw new \Exception($this->getSource().' пустой контент');
        }
        \libxml_use_internal_errors(true);
        $this->domDocument->loadHTML($content);
        $textareas = $this->domDocument->getElementsByTagName('textarea');
        foreach ($textareas as $textarea) {
            $textareaValue = $textarea->nodeValue;
            break;
        }

        $output = [];
        foreach (explode("\n", $textareaValue) as $entity) {
            $exploaded = explode(':', $entity);

            if (count($exploaded) == 2 && filter_var($exploaded[0], FILTER_VALIDATE_IP)) {
                $output[] = [
                    'address' => $entity,
                    'source' => $this->getSource()
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
