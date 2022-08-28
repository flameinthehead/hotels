<?php

namespace App\UseCase\Ostrovok;

class Facilities
{
    public const FACILITIES = [
        'has_internet' => 'Бесплатный интернет',
        'has_airport_transfer' => 'Трансфер',
        'has_parking' => 'Парковка',
        'has_pool' => 'Бассейн',
        'has_fitness' => 'Фитнес',
        'has_meal' => 'Бар или ресторан',
        'has_busyness' => 'Конференц-зал',
        'has_spa' => 'Спа-услуги',
        'has_ski' => 'Горнолыжный склон рядом',
        'beach' => 'Пляж рядом'
    ];

    public static function getFacilityByCode(string $code): ?string
    {
        return self::FACILITIES[$code] ?? null;
    }
}
