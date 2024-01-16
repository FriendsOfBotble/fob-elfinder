<?php

namespace FriendsOfBotble\ElFinder\Commands;

use Botble\Media\RvMedia;
use Botble\Media\Services\ThumbnailService;
use FriendsOfBotble\ElFinder\ElFinder;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

use function Laravel\Prompts\progress;

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('elfinder:thumbnail:generate', description: 'Generate thumbnails for images')]
class ThumbnailGenerateCommand extends Command
{
    public function __construct(
        protected ElFinder $elFinder,
        protected RvMedia $rvMedia,
        protected ThumbnailService $thumbnailService,
        protected Filesystem $files
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $root = $this->elFinder->getRoot();
        $disk = $this->elFinder->getDisk();

        $allFiles = $disk->allFiles();
        $this->components->info('Generating thumbnails for images in ' . $root);
        $progress = progress('Generating thumbnails for images in ' . $root, count($allFiles));
        $progress->start();

        foreach ($allFiles as $file) {
            $realPath = $disk->path($file);
            $basename = pathinfo($file, PATHINFO_BASENAME);

            if (Str::startsWith($basename, '.')) {
                $progress->label('Skipping ' . $file . ' because it is a hidden file');
                $progress->advance();

                continue;
            }

            if (! $this->rvMedia->canGenerateThumbnails($this->rvMedia->getMimeType($realPath))) {
                $progress->label('Skipping ' . $file . ' because it is not an image');
                $progress->advance();

                continue;
            }

            $progress->label('Generating thumbnails for ' . $file);
            $this->generateThumbnailsFor($realPath);
            $progress->advance();
        }

        $progress->finish();

        $this->components->info('Done. All images in ' . $root . ' have thumbnails generated.');

        return static::SUCCESS;
    }

    public function generateThumbnailsFor(string $realPath): void
    {
        foreach ($this->rvMedia->getSizes() as $size) {
            $readableSize = explode('x', $size);
            $destinationPath = str_replace($this->elFinder->getRoot(), '', $this->files->dirname($realPath));

            $this->thumbnailService
                ->setImage($realPath)
                ->setSize($readableSize[0], $readableSize[1])
                ->setDestinationPath($destinationPath)
                ->setFileName($this->files->name($realPath) . '-' . $size . '.' . $this->files->extension($realPath))
                ->save();
        }
    }
}
