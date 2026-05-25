<?php

namespace Johannhsdev\OctoLang\Setup;

final class KnownWelcomeHashes
{
    private const BLADE = [
        'a3e10115a61f683993652985cac5eeea', // Laravel 11 / 12
        // TODO: añadir hash de Laravel 13 tras fresh install antes del release v1.0
    ];

    private const REACT = [
        'e8de22d3cfb14be6110a4dd1c81dadf0', // Laravel 13 React starter kit (v13.4.0)
        // TODO: añadir hashes de Laravel 11/12 (Breeze) tras fresh install
    ];

    public static function contains(string $stack, string $hash): bool
    {
        $known = match ($stack) {
            'blade' => self::BLADE,
            'react' => self::REACT,
            default => [],
        };

        return in_array($hash, $known, strict: true);
    }
}
