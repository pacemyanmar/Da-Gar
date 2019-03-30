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
    'reporting_mode' => true,
    'double_entry' => false,
    'collapse' => true,
    'response_filter' => 'state_region',
    'verify_phone' => false,
    'allowedip' => [
        '172.20.0.1',
        '172.16.16.61',
        '116.212.155.142',
    ]
];