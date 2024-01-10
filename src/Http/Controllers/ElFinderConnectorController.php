<?php

namespace FriendsOfBotble\ElFinder\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Media\Facades\RvMedia;
use Botble\Media\Services\ThumbnailService;
use elFinder;
use elFinderConnector;
use FriendsOfBotble\ElFinder\ElFinder as ElFinderSupport;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ElFinderConnectorController extends BaseController
{
    public function __construct(
        protected ThumbnailService $thumbnailService,
        protected ElFinderSupport $elFinder
    ) {
    }

    public function __invoke()
    {
        elFinder::$netDrivers['ftp'] = 'FTP';

        $allowsMimes = collect(config('core.media.media.mime_types'))->flatten()->all();

        $opts = [
            'debug' => App::hasDebugModeEnabled(),
            'bind' => [
                'upload' => [$this, 'resize'],
            ],
            'roots' => [
                [
                    'driver' => 'LocalFileSystem',
                    'path' => tap($this->elFinder->getRoot(), fn ($path) => File::ensureDirectoryExists($path)),
                    'URL' => $baseUrl = '/' . $this->elFinder->getBasePath(),
                    'trashHash' => 't1_Lw',
                    'winHashFix' => DIRECTORY_SEPARATOR !== '/',
                    'uploadDeny' => ['all'],
                    'uploadAllow' => $allowsMimes,
                    'uploadOrder' => ['deny', 'allow'],
                    'accessControl' => [$this, 'access'],
                ],
                [
                    'id' => '1',
                    'driver' => 'Trash',
                    'path' => tap($this->elFinder->getTrashRoot(), fn ($path) => File::ensureDirectoryExists($path)),
                    'tmbURL' => $baseUrl . '/.tmb',
                    'winHashFix' => DIRECTORY_SEPARATOR !== '/',
                    'uploadDeny' => ['all'],
                    'uploadAllow' => $allowsMimes,
                    'uploadOrder' => ['deny', 'allow'],
                    'accessControl' => [$this, 'access'],
                ],
            ],
        ];

        $connector = new elFinderConnector(new elFinder($opts));

        $connector->run();
    }

    public function access(string $attr, string $path, string|null $data, object|null $volume, bool|null $isDir, string|null $relpath): bool|null
    {
        $basename = basename($path);

        $isHidden = ($basename[0] === '.' && strlen($relpath) !== 1)
            ? ! ($attr == 'read' || $attr == 'write')
            : null;

        if ($isHidden) {
            return $isHidden;
        }

        foreach (RvMedia::getSizes() as $size) {
            if ($isDir) {
                continue;
            }

            if (Str::of($path)->beforeLast('.')->endsWith($size)) {
                return true;
            }
        }

        return $isHidden;
    }

    public function resize($cmd, $result, $args, $elfinder, $volume)
    {
        $defaultDisk = config('filesystems.default');

        $this->elFinder->setDefaultDisk();

        foreach (RvMedia::getSizes() as $size) {
            $readableSize = explode('x', $size);

            if ($volume && $result && isset($result['added'])) {
                foreach ($result['added'] as $item) {
                    if (! $file = $volume->file($item['hash'])) {
                        continue;
                    }

                    $path = $volume->getPath($item['hash']);

                    if (! str_starts_with($file['mime'], 'image/')) {
                        continue;
                    }

                    $destinationPath = str_replace($this->elFinder->getRoot(), '', File::dirname($path));

                    $this->thumbnailService
                        ->setImage($path)
                        ->setSize($readableSize[0], $readableSize[1])
                        ->setDestinationPath($destinationPath)
                        ->setFileName(File::name($path) . '-' . $size . '.' . File::extension($path))
                        ->save();
                }
            }
        }

        config()->set('filesystem.default', $defaultDisk);
    }
}
