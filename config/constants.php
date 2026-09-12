<?php

return [

    /** Logo */
    'logo' => '',
    'logo_main' => '',

    'platform' => [
        'panel' => "CRM Panel",
        'app' => "Mobile Application",
    ],

    'web' => [
        'logo' => 'software/img/logo.png',
        'theme_primary_color' => '#000000',
        'theme_primary_color_light' => '#000000',
    ],

    'app' => [
        'logo' => 'software/img/logo.png',
        'theme_primary_color' => '#000000',
        'theme_primary_color_light' => '#000000',
    ],

    'permissions' => [
        'view' => 'view',
        'add' => 'add',
        'update' => 'update',
        'delete' => 'delete',
        'restore' => 'restore',
        'print' => 'print',
        'excel' => 'excel',
        'approval' => 'approval',
        'all_data' => 'all_data',
        'personal_data' => 'personal_data',
    ],

    'app_permissions' => [
        'view' => 'view',
        'add' => 'add',
        'update' => 'update',
        'delete' => 'delete',
        'approval' => 'approval',
        'all_data' => 'all_data',
        'personal_data' => 'personal_data',
    ],

    'firebase' => [
        'apiKey' => env('FIREBASE_API_KEY'),
        'authDomain' => env('FIREBASE_AUTH_DOMAIN'),
        'projectId' => env('FIREBASE_PROJECT_ID'),
        'messagingSenderId' => env('FIREBASE_MESSAGING_SENDER_ID'),
        'appId' => env('FIREBASE_APP_ID'),
        'vapidKey' => env('FIREBASE_VAPID_KEY'),
    ],

    'branch_type' => [
        'single' => "Single",
        'multiple' => "Multiple",
    ],

    'genders' => [
        'male' => "Male",
        'female' => "Female",
    ],

    'marital_status' => [
        'single' => "Single",
        'married' => "Married",
    ],

    'validation' => [
        'aadhar_card_number' => [
            'digits:12',
            'regex:/^[2-9]{1}[0-9]{11}$/'
        ],
        'pan_number' => [
            'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'
        ]
    ],
    'salary_classification' => [
        'PHS' => 'Per Hours Salary',
        'PDS' => 'Per Day Salary',
        'PMS' => 'Per Month Salary',
        'PWS' => 'Per Work Salary',
    ],
    'employee_code_auto_generation' => [
        'auto' => "Auto",
        'manual' => "Manual",
    ],
    'months' => [
        '' => 'All Months',
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December',
    ]
];
