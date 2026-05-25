<?php

namespace Johannhsdev\OctoLang\Facades;

use Illuminate\Support\Facades\Facade;
use Johannhsdev\OctoLang\LocaleManager;

/**
 * @method static void   set(string $locale)
 * @method static string current()
 * @method static bool   isSupported(string $locale)
 * @method static array  supported()
 * @method static string default()
 *
 * @see \Johannhsdev\OctoLang\LocaleManager
 */
class Locale extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LocaleManager::class;
    }
}
