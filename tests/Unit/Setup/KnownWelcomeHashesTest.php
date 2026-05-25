<?php

use Johannhsdev\OctoLang\Setup\KnownWelcomeHashes;

it('returns true for a known blade hash', function () {
    expect(KnownWelcomeHashes::contains('blade', 'a3e10115a61f683993652985cac5eeea'))->toBeTrue();
});

it('returns false for an unknown blade hash', function () {
    expect(KnownWelcomeHashes::contains('blade', 'ffffffffffffffffffffffffffffffff'))->toBeFalse();
});

it('returns false for unknown stacks', function () {
    expect(KnownWelcomeHashes::contains('unknown', 'a3e10115a61f683993652985cac5eeea'))->toBeFalse();
});
