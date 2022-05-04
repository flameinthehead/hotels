<?php

return [
    'telegram_request' => [
        'type' => 'state_machine',
        'marking_store' => [
            'property' => 'status', // this is the property on the model, defaults to 'marking'
        ],
        'supports' => ['App\Models\TelegramRequest'],
        'places' => ['new', 'city', 'check_in', 'check_out', 'adults'],
        'transitions' => [
            'choose_city' => [
                'from' => 'new',
                'to' => 'city',
            ],
            'choose_check_in' => [
                'from' => 'city',
                'to' => 'check_in',
            ],
            'choose_check_out' => [
                'from' => 'check_in',
                'to' => 'check_out',
            ],
            'choose_adults' => [
                'from' => 'check_out',
                'to' => 'adults',
            ]
        ],
    ],
];
