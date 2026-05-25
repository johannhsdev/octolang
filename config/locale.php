<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Supported Languages
    |--------------------------------------------------------------------------
    | List of locales the package will accept as valid.
    | You can extend them from .env with LOCALE_SUPPORTED (comma-separated).
    | Example .env: LOCALE_SUPPORTED=es,en,fr,pt
    */
    'supported' => array_filter(
        explode(',', env('LOCALE_SUPPORTED', 'es,en'))
    ),

    /*
    |--------------------------------------------------------------------------
    | Session Key
    |--------------------------------------------------------------------------
    | Name of the key used in session() to persist the active locale.
    */
    'session_key' => env('LOCALE_SESSION_KEY', 'locale'),

    /*
    |--------------------------------------------------------------------------
    | Route Name
    |--------------------------------------------------------------------------
    | Internal name of the POST route that changes the language.
    | Useful for form actions: route('locale.store')
    */
    'route_name' => 'locale.store',

    /*
    |--------------------------------------------------------------------------
    | Route URI
    |--------------------------------------------------------------------------
    */
    'route_uri' => '/locale',

    /*
    |--------------------------------------------------------------------------
    | Route Middleware
    |--------------------------------------------------------------------------
    | Middleware applied to the package's locale route.
    */
    'route_middleware' => ['web'],

];
