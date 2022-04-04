<?php
use App\UseCase\Proxy\Source\HideMyName;
use App\UseCase\Proxy\Source\Geonode;
use App\UseCase\Proxy\Source\RootJazz;
use App\UseCase\Proxy\Source\ProxyScrape;
use App\UseCase\Proxy\Source\FreeProxyListNet;
use App\UseCase\Proxy\Source\ProxySearcher;

return [
    'sources' => [
        HideMyName::SOURCE => HideMyName::class,
        Geonode::SOURCE => Geonode::class,
        RootJazz::SOURCE => RootJazz::class,
        ProxyScrape::SOURCE => ProxyScrape::class,
        FreeProxyListNet::SOURCE => FreeProxyListNet::class,
        ProxySearcher::SOURCE => ProxySearcher::class
    ]
];
