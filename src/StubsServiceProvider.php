<?php
declare(strict_types = 1);

namespace R2H\Stubs;

use Illuminate\Support\ServiceProvider;

class StubsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands(PublishCommand::class);
        }
    }
}
