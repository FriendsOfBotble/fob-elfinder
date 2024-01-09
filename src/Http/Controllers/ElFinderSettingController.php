<?php

namespace FriendsOfBotble\ElFinder\Http\Controllers;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Setting\Http\Controllers\SettingController;
use FriendsOfBotble\ElFinder\Forms\Settings\ElFinderSettingForm;
use FriendsOfBotble\ElFinder\Http\Requests\ElFinderSettingRequest;

class ElFinderSettingController extends SettingController
{
    public function edit()
    {
        $this->pageTitle(trans('plugins/elfinder::elfinder.settings.title'));

        return ElFinderSettingForm::create()->renderForm();
    }

    public function update(ElFinderSettingRequest $request): BaseHttpResponse
    {
        return $this->performUpdate($request->validated());
    }
}
