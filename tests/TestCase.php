<?php
declare(strict_types = 1);

namespace R2H\Stubs\Tests;

use R2H\Stubs\StubsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * Get the service providers for the package.
     *
     * @param  \Illuminate\Foundation\Application $app The application object.
     * @return array<int, \Orchestra\Testbench\Concerns\class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            StubsServiceProvider::class,
        ];
    }
}
