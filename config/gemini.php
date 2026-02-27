<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Gemini API Key
    |--------------------------------------------------------------------------
    |
    | Here you may specify your Gemini API Key and organization. This will be
    | used to authenticate with the Gemini API - you can find your API key
    | on Google AI Studio, at https://aistudio.google.com/app/apikey.
    */

    'api_key' => env('GEMINI_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Gemini Base URL
    |--------------------------------------------------------------------------
    |
    | If you need a specific base URL for the Gemini API, you can provide it here.
    | Otherwise, leave empty to use the default value.
    */
    'base_url' => env('GEMINI_BASE_URL'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout may be used to specify the maximum number of seconds to wait
    | for a response. By default, the client will time out after 30 seconds.
    */

    'request_timeout' => env('GEMINI_REQUEST_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Fallback Models (when rate limits are reached)
    |--------------------------------------------------------------------------
    |
    | When a model returns 429 RESOURCE_EXHAUSTED, the next model in this list
    | will be tried. Order: fastest/lightest first, then more capable models.
    | Free tier models: gemini-2.5-flash, gemini-2.0-flash, gemini-2.5-pro, etc.
    |
    */
    'fallback_models' => array_filter(array_map('trim', explode(',', env('GEMINI_FALLBACK_MODELS', '')))) ?: [
        'gemini-2.5-flash',      // Default: speed + cost-efficient
        'gemini-2.0-flash',      // Legacy, often has separate quota
        'gemini-2.5-pro',       // More capable, different rate limits
        'gemini-2.0-flash-exp',  // Experimental
    ],
];
