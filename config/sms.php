<?php
/**
 * Created by PhpStorm.
 * User: sithu
 * Date: 5/14/17
 * Time: 9:53 PM
 */

return [
    'type' => 'observer', // location or observer
    'second_locale' => [
        'country' => 'mm',
        'locale' => 'my'
    ],
    'double_entry' => false,


    // export columns
    'export_columns' => [
        'location_code' => 'location_code',
        'call_primary' => 'call_primary',
        'sms_time' => 'sms_time',
    ]
];