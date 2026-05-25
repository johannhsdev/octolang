<?php

namespace Johannhsdev\OctoLang\Tests;

use Johannhsdev\OctoLang\OctoLangServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            OctoLangServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('app.locale', 'es');
        $app['config']->set('locale.supported', ['es', 'en', 'fr']);
        $app['config']->set('locale.session_key', 'locale');
        $app['config']->set('locale.route_uri', '/locale');
        $app['config']->set('locale.route_name', 'locale.store');
        $app['config']->set('locale.route_middleware', ['web']);
        $app['config']->set('session.driver', 'array');
        $app['config']->set('view.paths', [__DIR__.'/Fixtures/views']);
    }
}
