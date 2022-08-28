<?php

return [
    'telegram_request' => [
        'type' => 'state_machine',
        'marking_store' => [
            'property' => 'status', // this is the property on the model, defaults to 'marking'
        ],
        'supports' => ['App\Models\TelegramRequest'],
        'places' => ['new', 'city', 'check_in', 'check_out', 'adults', 'stars'],
        'transitions' => [
            'choose_city' => [
                'from' => 'new',
                'to' => 'city',
                'metadata' => [
                    'next_message' => 'Выберите дату заезда',
                    'need_calendar' => true,
                ]
            ],
            'choose_check_in' => [
                'from' => 'city',
                'to' => 'check_in',
                'metadata' => [
                    'next_message' => 'Выберите дату отъезда',
                    'need_calendar' => true,
                    'selected_date_message' => 'Выбранная дата заезда'
                ]
            ],
            'choose_check_out' => [
                'from' => 'check_in',
                'to' => 'check_out',
                'metadata' => [
                    'next_message' => 'Введите количество взрослых',
                    'selected_date_message' => 'Выбранная дата отъезда'
                ]
            ],
            'choose_adults' => [
                'from' => 'check_out',
                'to' => 'adults',
                'metadata' => [
                    'next_message' => 'Выберите минимальное количество звёзд',
                    'need_stars' => true
                ],
            ],
            'choose_minimum_stars' => [
                'from' => 'adults',
                'to' => 'stars',
                'metadata' => [
                    'is_final_message' => true
                ],
            ]
        ],
    ],
];
