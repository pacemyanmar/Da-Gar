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
            'provider' => 'blueplanet',
            'use' => true
        ],
        'blueplanet' => [
            'api_url' => 'https://boomsms.net/api/sms/json',
            'access_token' => '',
            'sender_id' => 'PACE'
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