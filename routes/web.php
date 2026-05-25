<?php

use Illuminate\Support\Facades\Route;
use Johannhsdev\OctoLang\Http\Controllers\LocaleController;

Route::post(
    config('locale.route_uri', '/locale'),
    [LocaleController::class, 'store']
)->middleware(config('locale.route_middleware', ['web']))
 ->name(config('locale.route_name', 'locale.store'));
