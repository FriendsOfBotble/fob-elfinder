<?php

namespace FriendsOfBotble\ElFinder;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Setting\Facades\Setting;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Setting::delete([
            'elfinder_editor_enabled',
            'elfinder_replace_default_media',
            'elfinder_base_path',
        ]);
    }
}
