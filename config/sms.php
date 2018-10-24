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
    'double_entry' => false,
    'collapse' => true,
    'allowedip' => [
        '172.20.0.1',
        '172.16.16.61',
        '116.212.155.142',
    ],

    // export columns
    'export_columns' => [
        'location_code' => 'location_code',
        'observer_name' => 'observer_name',
        'call_primary' => 'call_primary',
        'sms_primary' => 'sms_primary',
        //'sms_time' => 'sms_time',
        'observer_field' => 'observer_field',
    ],
    'incident_columns' => [
        'location_code' => 'location_code',
        'observer_name' => 'observer_name',
        'incident_center' => 'incident_center',
        'sms_primary' => 'sms_primary',
        //'sms_time' => 'sms_time',
        'observer_field' => 'observer_field',
    ]
];