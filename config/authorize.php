<?php
return [
    'login_id'        => env('LOGIN_ID', '77nyJ8R4dx'),
    'transaction_key' => env('TRANSACTION_KEY', '2wrbG4RPqm78f34P'),
    'sandbox'         => env('AUTHORIZE_SANDBOX', true),

    'endpoint' => [
        'sandbox'    => 'https://test.authorize.net/customer/addPayment',
        'production' => 'https://accept.authorize.net/customer/addPayment',
    ],
];