<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'whatsapp' => [
        'provider' => env('WHATSAPP_PROVIDER', 'meta'), // 'meta' or 'legacy'
        'token' => env('WHATSAPP_CLOUD_TOKEN', env('WHATSAPP_ACCESS_TOKEN', null)),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID', null),
        'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID', null),
        'api_version' => env('WHATSAPP_API_VERSION', 'v21.0'),
        'otp_template_name' => env('WHATSAPP_OTP_TEMPLATE_NAME', null),
        'otp_template_lang' => env('WHATSAPP_OTP_TEMPLATE_LANG', 'es'),
        'otp_button_type' => env('WHATSAPP_OTP_BUTTON_TYPE', null),
    ],

];
