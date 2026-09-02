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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'firebase' => [
        'credentials' => env('FIREBASE_CREDENTIALS'),
        'project_id' => env('FIREBASE_PROJECT_ID', 'ngebadmintonyuk'),
        'web' => [
            'apiKey' => env('FIREBASE_WEB_API_KEY', 'AIzaSyChFJ3ChSD6oHqnWmFOh0aUecdM9chqrSs'),
            'authDomain' => env('FIREBASE_WEB_AUTH_DOMAIN', 'ngebadmintonyuk.firebaseapp.com'),
            'projectId' => env('FIREBASE_PROJECT_ID', 'ngebadmintonyuk'),
            'storageBucket' => env('FIREBASE_WEB_STORAGE_BUCKET', 'ngebadmintonyuk.firebasestorage.app'),
            'messagingSenderId' => env('FIREBASE_MESSAGING_SENDER_ID', '4302480298'),
            'appId' => env('FIREBASE_WEB_APP_ID', '1:4302480298:web:3d19aac67c701b0f9681d3'),
        ],
        'vapid_key' => env('FIREBASE_VAPID_KEY', 'BFIQDfeBTND-UDw-UMurboznvoPzZZ9IFtT5Uq1QQIH5If5uUczJOPLfE8UtTovgGCeEX30ROH0cGWqJM9IcwMQ'),
    ],

];
