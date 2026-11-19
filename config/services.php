<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sepay' => [
        'webhook_secret' => env('SEPAY_WEBHOOK_SECRET'),
        'bank_code' => env('SEPAY_BANK_CODE'),
        'bank_name' => env('SEPAY_BANK_NAME'),
        'bank_account' => env('SEPAY_BANK_ACCOUNT'),
        'bank_account_name' => env('SEPAY_BANK_ACCOUNT_NAME'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'curated_ai' => [
        'api_key' => env('CURATED_AI_KEY', env('GEMINI_API_KEY', env('OPENAI_API_KEY'))),
        'provider' => env('CURATED_AI_PROVIDER', 'gemini'),
        'timeout' => 5,
    ],

    'mayhen_ai' => [
        'enabled' => filter_var(env('MAYHEN_AI_ENABLED', true), FILTER_VALIDATE_BOOL),
        'base_url' => rtrim((string) env('MAYHEN_AI_BASE_URL', 'https://api.openai.com/v1'), '/'),
        'model' => (string) env('MAYHEN_AI_MODEL', 'gpt-6.1-sol'),
        'wire_api' => (string) env('MAYHEN_AI_WIRE_API', 'responses'),
        'bearer_token' => (string) env('MAYHEN_AI_BEARER_TOKEN', ''),
        'timeout' => (int) env('MAYHEN_AI_TIMEOUT', 15),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

];
