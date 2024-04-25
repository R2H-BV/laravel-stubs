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

    protected $signature = 'r2h-stubs:publish {--force : Overwrite any existing files.}';
    protected $description = 'Publish all opiniated stubs that are available for customization.';

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
            // @phpstan-ignore-next-line
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
     * @param Collection<int, SplFileInfo> $files
     * @return Collection<int, SplFileInfo>
     */
    private function unpublished(Collection $files): Collection
    {
        return $files->reject(fn (SplFileInfo $file): bool => file_exists($this->targetPath($file)));
    }

    /**
     * Publish the given files to the target location.
     *
     * @param Collection<int, SplFileInfo> $files
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
     */
    private function targetPath(SplFileInfo $file): string
    {
        $directory = str($file->getPath())->after('/stubs')->toString();
        return "{$this->laravel->basePath('stubs')}{$directory}/{$file->getFilename()}";
    }
}
