<?php

namespace App\UseCase\Proxy\Source;

use App\UseCase\Proxy\SourceInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;

class ProxySearcher implements SourceInterface
{
    public const SOURCE = 'proxysearcher';
    public const HTTP_BASE_URL = 'http://proxysearcher.sourceforge.net/Proxy%20List.php?type=http&filtered=true';
    public const SOCKS_BASE_URL = 'http://proxysearcher.sourceforge.net/Proxy%20List.php?type=socks&filtered=true';

    public function __construct(private Client $client, private \DOMDocument $domDocument)
    {
    }

    public function parse(): Collection
    {
        $responseHttpProxy = $this->processRequest($this->client->request('GET', self::HTTP_BASE_URL));
        $responseSocksProxy = $this->processRequest($this->client->request('GET', self::SOCKS_BASE_URL));
        return collect(array_merge($responseHttpProxy, $responseSocksProxy));
    }

    public function getSource(): string
    {
        return self::SOURCE;
    }

    private function processRequest($response): array
    {
        if ($response->getStatusCode() !== 200) {
            throw new \Exception($this->getSource().' Код ответа http proxy != 200');
        }

        $content = $response->getBody()->getContents();
        if (empty($content)) {
            throw new \Exception($this->getSource().' Пустой контент http proxy');
        }

        libxml_use_internal_errors(true);
        $this->domDocument->loadHTML($content);
        $proxyTable = $this->domDocument->getElementById('proxyTable');

        $tdList = $proxyTable->getElementsByTagName('td');
        $output = [];
        foreach ($tdList as $td) {
            $exploaded = explode(':', $td->nodeValue);

            if (count($exploaded) == 2 && filter_var(reset($exploaded), FILTER_VALIDATE_IP)) {
                $output[] = [
                    'address' => reset($exploaded).':'.$exploaded[1],
                    'source' => $this->getSource(),
                ];
            }
        }

        return $output;
    }
}
