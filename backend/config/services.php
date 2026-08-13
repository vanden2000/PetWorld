<?php

$chatbotProvider = strtolower((string) env('CHATBOT_PROVIDER', 'gemini'));

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
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // SePay: xác nhận chuyển khoản tự động qua webhook.
    // (Ảnh VietQR được frontend dựng từ NEXT_PUBLIC_SEPAY_QR_BASE, không cần ở đây.)
    'sepay' => [
        // Khoá bí mật để xác thực webhook (header "Authorization: Apikey <key>").
        'webhook_api_key' => env('SEPAY_WEBHOOK_API_KEY'),
        // Token Bearer để backend chủ động gọi SePay API khi chạy local không dùng webhook/ngrok.
        'api_token' => env('SEPAY_API_TOKEN'),
        'api_base_url' => rtrim((string) env('SEPAY_API_BASE_URL', 'https://userapi.sepay.vn/v2'), '/'),
    ],

    'ghtk' => [
        'api_token' => env('GHTK_API_TOKEN'),
        'client_source' => env('GHTK_CLIENT_SOURCE'),
        'base_url' => rtrim((string) env('GHTK_BASE_URL', 'https://services.giaohangtietkiem.vn'), '/'),
        'timeout' => (int) env('GHTK_TIMEOUT', 15),
        'pickup_address_id' => env('GHTK_PICKUP_ADDRESS_ID'),
        'pickup_name' => env('GHTK_PICKUP_NAME'),
        'pickup_phone' => env('GHTK_PICKUP_PHONE'),
    ],

    'ghn' => [
          'api_token' => env('GHN_TOKEN'),
          'shop_id' => env('GHN_SHOP_ID'),
          'webhook_secret' => env('GHN_WEBHOOK_SECRET'),
          'base_url' => rtrim((string) env('GHN_BASE_URL', 'https://dev-online-gateway.ghn.vn'), '/'),
        'timeout' => (int) env('GHN_TIMEOUT', 15),
        'from_name' => env('GHN_FROM_NAME'),
        'from_phone' => env('GHN_FROM_PHONE'),
        'from_address' => env('GHN_FROM_ADDRESS'),
        'from_ward_name' => env('GHN_FROM_WARD_NAME'),
        'from_district_name' => env('GHN_FROM_DISTRICT_NAME'),
        'from_province_name' => env('GHN_FROM_PROVINCE_NAME'),
        'from_district_id' => env('GHN_FROM_DISTRICT_ID'),
        'from_ward_code' => env('GHN_FROM_WARD_CODE'),
        'required_note' => env('GHN_REQUIRED_NOTE', 'KHONGCHOXEMHANG'),
        'payment_type_id' => (int) env('GHN_PAYMENT_TYPE_ID', 1),
        'service_type_id' => (int) env('GHN_SERVICE_TYPE_ID', 2),
        'length' => (int) env('GHN_PACKAGE_LENGTH', 20),
        'width' => (int) env('GHN_PACKAGE_WIDTH', 20),
        'height' => (int) env('GHN_PACKAGE_HEIGHT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google AI Studio / Gemini
    |--------------------------------------------------------------------------
    |
    | Chatbot uses this configuration only on the Laravel backend. No API call
    | is made at this stage, and the API key must never be exposed to frontend.
    |
    */
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL'),
        'base_url' => rtrim((string) env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'), '/'),
        'timeout' => (int) env('GEMINI_TIMEOUT', 30),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'base_url' => rtrim((string) env('OPENAI_BASE_URL', 'https://api.openai.com/v1'), '/'),
        'timeout' => (int) env('OPENAI_TIMEOUT', 30),
    ],

    // Chatbot can use OpenAI directly or Gemini through its OpenAI-compatible API.
    'chatbot' => [
        'provider' => $chatbotProvider,
        'api_key' => $chatbotProvider === 'gemini'
            ? env('GEMINI_API_KEY')
            : env('OPENAI_API_KEY'),
        'model' => env('CHATBOT_MODEL', $chatbotProvider === 'gemini'
            ? 'gemini-3.1-flash-lite'
            : env('OPENAI_MODEL', 'gpt-4o-mini')),
        'base_url' => rtrim((string) env('CHATBOT_BASE_URL', $chatbotProvider === 'gemini'
            ? 'https://generativelanguage.googleapis.com/v1beta/openai'
            : env('OPENAI_BASE_URL', 'https://api.openai.com/v1')), '/'),
        'timeout' => (int) env('CHATBOT_TIMEOUT', $chatbotProvider === 'gemini'
            ? env('GEMINI_TIMEOUT', 30)
            : env('OPENAI_TIMEOUT', 30)),
    ],

];
