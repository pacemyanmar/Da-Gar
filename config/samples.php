<?php

return [
    'locations' => [
        'location_code' => [
            'title' => 'Location Code',
            'primary' => true,
            'foreign' => false, // foreign key
            'show' => true
        ],
        'level1' => [
            'title' => 'State/Region',
            'show' => true
        ],
        'level2' => [
            'title' => 'District',
        ],
        'level3' => [
            'title' => 'Township',
            'show' => true
        ],
        'level4' => [
            'title' => 'Village Tract/Ward',
            'show' => true
        ],
        'level5' => [
            'title' => 'Village',
        ],
        'area_type' => [
            'title' => 'Rural/Urban'
        ],
        'sample' => [
            'title' => 'Sample Type'
        ],
        'parties' => [
            'title' => 'Parties',
        ]

    ],
    'observers' => [
        'code' => [
            'title' => 'Observer Code',
            'primary' => true,
            'notnull' => true
        ],
        'full_name' => [
            'title' => 'Name',
            'notnull' => true
        ],
        'national_id' => [
            'title' => 'NRC ID'
        ],
        'address' => [
            'title' => 'Address'
        ],
        'phone_1' => [
            'title' => 'Phone 1'
        ],
        'phone_2' => [
            'title' => 'Phone 2'
        ],
        'gender' => [
            'title' => 'Gender'
        ],
        'education' => [
            'title' => 'Education'
        ],
        'ethincity' => [
            'title' => 'Ethincity'
        ],
        'dob' => [
            'title' => 'Date of Birth',
            'type' => 'date'
        ]

    ],
    'unique' => 'observer'
];