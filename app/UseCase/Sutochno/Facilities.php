<?php

namespace App\UseCase\Sutochno;

class Facilities
{
    public const FACILITIES = [
        'condition' => 'Кондиционер',
        'wi-fi' => 'Wi-Fi',
        'jacuzzi' => 'Джакузи',
        'kitchen' => 'Кухня',
        'balcony' => 'Балкон/лоджия',
        'sauna' => 'Сауна/баня',
        'refrigerator' => 'Холодильник',
        'tv' => 'Телевизор',
        'kettle' => 'Электрочайник',
        'crockery' => 'Посуда и принадлежности',
        'dishwasher' => 'Посудомоечная машина',
        'towels' => 'Полотенца',
        'microwave' => 'Микроволновка',
        'multicooker' => 'Мультиварка',
        'washmachine' => 'Стиральная машина',
        'hairdryer' => 'Фен',
        'iron' => 'Yтюг с гладильной доской',
        'terrace' => 'Терраса'
    ];

    public static function getFacilityByCode(string $code): ?string
    {
        return self::FACILITIES[$code] ?? null;
    }
}
