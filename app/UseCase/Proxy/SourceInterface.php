<?php

namespace App\UseCase\Proxy;


use GuzzleHttp\Client;
use Illuminate\Support\Collection;

interface SourceInterface
{
    public function __construct(Client $client);

    public function parse(): Collection;

    public function getSource(): string;
}
