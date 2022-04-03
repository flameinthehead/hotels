<?php
use App\UseCase\Proxy\Source\HideMyName;
use App\UseCase\Proxy\Source\Geonode;
use App\UseCase\Proxy\Source\RootJazz;
use App\UseCase\Proxy\Source\ProxyScrape;

return [
    'sources' => [
        HideMyName::SOURCE => HideMyName::class,
        Geonode::SOURCE => Geonode::class,
        RootJazz::SOURCE => RootJazz::class,
        ProxyScrape::SOURCE => ProxyScrape::class,
        \App\UseCase\Proxy\Source\FreeProxyListNet::SOURCE => \App\UseCase\Proxy\Source\FreeProxyListNet::class
    ]
];
