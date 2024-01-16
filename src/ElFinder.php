<?php

namespace FriendsOfBotble\ElFinder;

use Botble\Base\Facades\Assets;
use Illuminate\Contracts\Container\Container;
use Illuminate\Filesystem\FilesystemAdapter;

class ElFinder
{
    public function __construct(protected Container $container)
    {
    }

    public function registerConnectorScript(): void
    {
        add_filter(BASE_FILTER_HEAD_LAYOUT_TEMPLATE, function (string|null $rendered) {
            if ($this->container['elfinder.connector.registered']) {
                return $rendered;
            }

            $this->container->instance('elfinder.connector.registered', true);

            return $rendered . view('plugins/elfinder::includes.header')->render();
        }, 120);
    }

    public function registerAssets(): void
    {
        if ($this->container['elfinder.assets.registered']) {
            return;
        }

        Assets::addScripts('jquery-ui')->addStyles('jquery-ui');

        Assets::addStylesDirectly('vendor/core/plugins/elfinder/css/elfinder.min.css')
            ->addStylesDirectly('vendor/core/plugins/elfinder/css/theme.css')
            ->addStylesDirectly('vendor/core/plugins/elfinder/css/elfinder-integration.css')
            ->addScriptsDirectly('vendor/core/plugins/elfinder/js/elfinder.min.js')
            ->addScriptsDirectly('vendor/core/plugins/elfinder/js/elfinder-integration.js');

        $this->container->instance('elfinder.assets.registered', true);
    }

    public function getBasePath(): string
    {
        return setting('elfinder_base_path', 'storage');
    }

    public function getTrashRoot(): string
    {
        return $this->getRoot() . '/.trash';
    }

    public function getRoot(): string
    {
        return public_path($this->getBasePath());
    }

    public function getDisk(): FilesystemAdapter
    {
        return $this->container['filesystem']->disk('elfinder');
    }

    public function registerFilesystemDisk(): void
    {
        $config = $this->container['config'];
        $config->set('filesystems.disks.elfinder', [
            'driver' => 'local',
            'root' => $this->getRoot(),
            'url' => $url = url($basePath = $this->getBasePath()),
            'visibility' => 'public',
        ]);

        if (setting('elfinder_replace_default_media', false)) {
            $config->set([
                'core.media.media.default_upload_folder' => $basePath,
                'core.media.media.default_upload_url' => $url,
            ]);

            $this->setDefaultDisk();
        }
    }

    public function setDefaultDisk(): void
    {
        $this->container['config']->set('filesystems.default', 'elfinder');
    }
}
