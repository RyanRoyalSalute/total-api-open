<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'sms' => [
        'username' => env('SMS_API_USERNAME'), // Username from .env
        'password' => env('SMS_API_PASSWORD'), // Password from .env
        'url' => 'https://smsapi.mitake.com.tw/api/mtk/SmSend', // SMS API URL
    ],

    'ecpay' => [
        'merchant_id' => env('ECPAY_MERCHANT_ID'),
        'hash_key' => env('ECPAY_HASH_KEY'),
        'hash_iv' => env('ECPAY_HASH_IV'),
        'server' => env('ECPAY_SERVER', 'https://payment.ecpay.com.tw/Cashier/AioCheckOut/V5'),
        'return_url' => env('ECPAY_RETURN_URL'),
        'client_back_url' => env('ECPAY_BACK_URL'),
    ],

    'linepay' => [
        'channel_id' => env('LINE_PAY_CHANNEL_ID'),
        'channel_secret' => env('LINE_PAY_CHANNEL_SECRET'),
        'is_sandbox' => env('LINE_PAY_IS_SANDBOX', true),
        'confirm_url' => env('LINE_PAY_CONFIRM_URL'),
        'cancel_url' => env('LINE_PAY_CANCEL_URL'),
    ],
    
];
