<?php
// cspell:ignore authkey appkey

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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'github' => [
        'client_id' => 'YOUR_GITHUB_API', //Github API
        'client_secret' => 'YOUR_GITHUB_SECRET', //Github Secret
        'redirect' => env('APP_URL') . '/login/github/callback',
    ],
    'google' => [
        'client_id' => 'YOUR_GOOGLE_API', //Google API
        'client_secret' => 'YOUR_GOOGLE_SECRET', //Google Secret
        'redirect' => env('APP_URL') . '/login/google/callback',
    ],
    'facebook' => [
        'client_id' => 'YOUR_FACEBOOK_API', //Facebook API
        'client_secret' => 'YOUR_FACEBOK_SECRET', //Facebook Secret
        'redirect' => env('APP_URL') . '/login/facebook/callback',
    ],
    'whatsapp' => [
        'api_url' => env('WHATSAPP_URL'),
        'test_number' => env('TEST_WHATSAPP'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'bearer_token' => env('WHATSAPP_BEARER_TOKEN'),
    ],


];
