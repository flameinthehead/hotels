<?php
use App\UseCase\Proxy\Source\HideMyName;
use App\UseCase\Proxy\Source\Geonode;
use App\UseCase\Proxy\Source\RootJazz;

return [
    'sources' => [
        HideMyName::SOURCE => HideMyName::class,
        Geonode::SOURCE => Geonode::class,
        RootJazz::SOURCE => RootJazz::class
    ]
];
