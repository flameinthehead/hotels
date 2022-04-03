<?php

namespace App\UseCase\Proxy;


use GuzzleHttp\Client;
use Illuminate\Support\Collection;

interface SourceInterface
{
    public function parse(): Collection;

    public function getSource(): string;
}
