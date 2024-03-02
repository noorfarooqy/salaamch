<?php


return [
    'login' => env('SCH_LOGIN'),
    'password' => env('SCH_PASSWORD'),
    'secret' => env('SCH_SECRET'),
    'product' => env('SCH_PRODUCT'),
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
    'host' => [
        'uri' => env('SCH_HOST_URI'),
        'api_rate' => env('SCH_HOST_RATE_API'),
        'api_deposit_success' => env('SCH_DEPOSIT_SUCCESS_API'),
        'api_deposit_failed' => env('SCH_DEPOSIT_FAILED_API'),
    ],
];
