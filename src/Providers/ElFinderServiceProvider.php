<?php

namespace FriendsOfBotble\ElFinder\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\PanelSections\PanelSectionItem;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Setting\PanelSections\SettingOthersPanelSection;
use FriendsOfBotble\ElFinder\Commands\ThumbnailGenerateCommand;
use FriendsOfBotble\ElFinder\ElFinder;
use Illuminate\Routing\Events\RouteMatched;

class ElFinderServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/elfinder')
            ->loadAndPublishConfigurations(['permissions'])
            ->loadAndPublishTranslations()
            ->loadRoutes()
            ->loadAndPublishViews()
            ->publishAssets();

        $this->app['files']->requireOnce(__DIR__ . '/../../connector/autoload.php');

        $this->app['events']->listen(RouteMatched::class, function () {
            $this->app['elfinder']->registerConnectorScript();

            add_filter(BASE_FILTER_FORM_EDITOR_BUTTONS, function (string|null $rendered) {
                $this->app['elfinder']->registerAssets();

                return $rendered;
            }, 120);

            add_filter('core_base_media_after_assets', function (string|null $rendered) {
                $this->app['elfinder']->registerAssets();

                return $rendered;
            }, 120);
        });

        $this->app->booted(fn ($app) => $app['elfinder']->registerFilesystemDisk());

        PanelSectionManager::default()->beforeRendering(function () {
            PanelSectionManager::registerItem(
                SettingOthersPanelSection::class,
                fn () => PanelSectionItem::make('elfinder')
                    ->setTitle(trans('plugins/elfinder::elfinder.settings.title'))
                    ->withIcon('ti ti-folder')
                    ->withPriority(800)
                    ->withDescription(trans('plugins/elfinder::elfinder.settings.description'))
                    ->withRoute('elfinder.settings')
            );
        });

        DashboardMenu::default()->beforeRetrieving(function () {
            if ($replaceDefaultMedia = setting('elfinder_replace_default_media', false)) {
                DashboardMenu::removeItem('cms-core-media');
            }

            DashboardMenu::make()
                ->registerItem([
                    'id' => 'cms-elfinder-media',
                    'priority' => 999,
                    'icon' => 'ti ti-folder',
                    'name' => $replaceDefaultMedia ? 'core/media::media.menu_name' : 'plugins/elfinder::elfinder.menu_name',
                    'route' => 'elfinder.index',
                    'permissions' => ['elfinder.index'],
                ]);
        });

        if ($this->app->runningInConsole()) {
            $this->commands([ThumbnailGenerateCommand::class]);
        }
    }

    public function register(): void
    {
        $this->app->singleton('elfinder', function () {
            return new ElFinder($this->app);
        });

        $this->app->alias('elfinder', ElFinder::class);

        $this->app->instance('elfinder.assets.registered', false);
        $this->app->instance('elfinder.connector.registered', false);
    }
}
