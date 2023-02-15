<?php
declare(strict_types = 1);

namespace R2H\Stubs\Tests;

use Illuminate\Support\Facades\File;

class PublishCommandTest extends TestCase
{
    /**
     * Test if the command publishes the stubs.
     *
     * @return void
     * @test
     */
    public function it_can_publish_stubs(): void
    {
        $targetStubsPath = $this->app->basePath('stubs');
        File::deleteDirectory($targetStubsPath);

        $this
            ->artisan('r2h-stubs:publish')
            ->expectsOutput('53 / 53 stubs published.')
            ->assertExitCode(0);

        $stubPath = __DIR__ . '/../stubs/migration.stub';
        $publishedStubsPath = $targetStubsPath . '/migration.stub';
        $this->assertFileEquals($stubPath, $publishedStubsPath);
    }
}
