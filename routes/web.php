<?php

use App\Http\Middleware\VerifyCsrfToken;
use Botble\Base\Facades\AdminHelper;
use FriendsOfBotble\ElFinder\Http\Controllers\ElFinderConnectorController;
use FriendsOfBotble\ElFinder\Http\Controllers\ElFinderController;
use FriendsOfBotble\ElFinder\Http\Controllers\ElFinderSettingController;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function () {
    Route::group(['prefix' => 'elfinder', 'as' => 'elfinder.', 'permission' => 'elfinder.index'], function () {
        Route::get('', [ElFinderController::class, 'index'])
            ->name('index');

        Route::match(['GET', 'POST'], 'connector', [ElFinderConnectorController::class, '__invoke'])
            ->name('connector')
            ->withoutMiddleware(VerifyCsrfToken::class);
    });

    Route::group(['prefix' => 'settings'], function () {
        Route::group(['prefix' => 'elfinder', 'as' => 'elfinder.', 'permission' => 'elfinder.settings'], function () {
            Route::get('', [ElFinderSettingController::class, 'edit'])
                ->name('settings');

            Route::put('', [ElFinderSettingController::class, 'update'])
                ->name('settings.update');
        });
    });
});
