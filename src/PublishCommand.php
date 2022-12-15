<?php
declare(strict_types = 1);

namespace R2H\Stubs;

use SplFileInfo;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Console\ConfirmableTrait;

class PublishCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'r2h-stubs:publish {--force : Overwrite any existing files.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish all opiniated stubs that are available for customization.';

    /**
     * Execute the console command.
     *
     * @return integer
     */
    public function handle(): int
    {
        // Check if the command is being ran in production.
        if (!$this->confirmToProceed()) {
            return Command::FAILURE;
        }

        // Create the stubs path if it doesn't yet exist.
        if (!is_dir($stubsPath = $this->laravel->basePath('stubs'))) {
            (new Filesystem)->makeDirectory($stubsPath);
        }

        // Retrieve the stubs that should be published.
        $files = collect(File::allFiles(__DIR__ . '/../stubs'))->unless(
            $this->option('force'),
            fn (Collection $files): Collection => $this->unpublished($files)
        );

        // Publish the stubs to the target location.
        $published = $this->publish($files);

        $this->info("{$published} / {$files->count()} stubs published.");

        return Command::SUCCESS;
    }

    /**
     * Retrieve the list of files that are not yet published.
     *
     * @param  \Illuminate\Support\Collection $files The file list.
     * @return \Illuminate\Support\Collection
     */
    private function unpublished(Collection $files): Collection
    {
        return $files->reject(fn (SplFileInfo $file): bool => file_exists($this->targetPath($file)));
    }

    /**
     * Publish the given files to the target location.
     *
     * @param  \Illuminate\Support\Collection $files The file list.
     * @return integer
     */
    private function publish(Collection $files): int
    {
        return $files->reduce(function (int $published, SplFileInfo $file): int {
            // Create the target directory if it doesn't yet exist.
            if (!is_dir($target = str($this->targetPath($file))->beforeLast('/')->toString())) {
                (new Filesystem)->makeDirectory($target);
            }

            file_put_contents($this->targetPath($file), file_get_contents($file->getPathname()));

            return $published + 1;
        }, 0);
    }

    /**
     * Retrieve the target path of a given file.
     *
     * @param  \SplFileInfo $file The file object.
     * @return string
     */
    private function targetPath(SplFileInfo $file): string
    {
        $directory = str($file->getPath())->after('/stubs')->toString();
        return "{$this->laravel->basePath('stubs')}{$directory}/{$file->getFilename()}";
    }
}
