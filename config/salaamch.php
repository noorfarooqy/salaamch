<?php


return [
    'login' => env('SCH_LOGIN'),
    'password' => env('SCH_PASSWORD'),
    'secret' => env('SCH_SECRET'),
    'endpoints' => [
        'root' => env('SCH_API_ROOT'),
        'methods' => [
            'verification' => [
                'api' => '/api/values/bankAccountInformation',
                'name' => 'bankAccountInformation'
            ],
            'status' => [
                'api' => '/api/values/bankTransactionStatus',
                'name' => 'bankTransactionStatus'
            ],
            'deposit' => [
                'api' => '/api/values/bankDepositToAccount',
                'name' => 'bankDepositToAccount'
            ]
        ],
    ],
];