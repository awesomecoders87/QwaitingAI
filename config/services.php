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


 'stripe' => [
            'secret' => env('STRIPE_SECRET'),
            'key' => env('STRIPE_KEY'),
    ],

     'microsoft' => [
        'client_id' => env('MICROSOFT_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
        'tenant' => 'common',
        'redirect' => env('MICROSOFT_REDIRECT_URI'),
    ],

    'okta' => [
        'client_id'     => env('OKTA_CLIENT_ID'),
        'client_secret' => env('OKTA_CLIENT_SECRET'),
        'redirect'      => env('OKTA_REDIRECT_URI'),
        'base_url'      => env('OKTA_BASE_URL'), // required for SocialiteProviders
    ],
    'openai' => [
         'key' => env('OPENAI_API_KEY'),
    ],

    'gupshup' => [
        'api_key' => env('GUPSHUP_API_KEY'),
        'base_url' => env('GUPSHUP_BASE_URL'),
    ],

    'salesforce' => [
    'client_id' => env('SALESFORCE_CLIENT_ID'),
    'client_secret' => env('SALESFORCE_CLIENT_SECRET'),
    'username' => env('SALESFORCE_USERNAME'),
    'password' => env('SALESFORCE_PASSWORD'),
],

];
