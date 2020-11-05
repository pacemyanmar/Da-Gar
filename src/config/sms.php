<?php
/**
 * Created by PhpStorm.
 * User: sithu
 * Date: 5/14/17
 * Time: 9:53 PM
 */

return [
    'type' => 'location', // location or observer
    'primary_locale' => [
        'country' => 'us',
        'locale' => 'en'
    ],
    'second_locale' => [
        'country' => 'mm',
        'locale' => 'my'
    ],
    'providers' => [
        'global' => [
            'provider' => 'blueplanet2',
            'use' => true
        ],
        'blueplanet' => [
            'active' => env('BP_ACTIVE', 'api2'),
            'api1' =>
            [
            'api_url' => 'https://boomsms.net/api/sms/json',
            'access_token' => '',
            'sender_id' => 'PACE',
            ],
            'api2' => [
                'api_url' => ' http://apiv2.blueplanet.com.mm/mptsdp/bizsendsmsapi.php',
                'sender_id' => 'PACE',
                'username' => env('BP_USERNAME'),
                'password' => env('BP_PASSWORD')
            ]
        ]
    ],
    'country_prefix' => '95',
    'reporting_mode' => false,
    'double_entry' => false,
    'collapse' => true,
    'response_filter' => 'state_region',
    'verify_phone' => true,
    'font_converter' => false,
    'allowedip' => [
        '172.20.0.1',
        '172.16.16.61',
        '116.212.155.142',
    ]
];
