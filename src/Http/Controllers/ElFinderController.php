<?php

namespace FriendsOfBotble\ElFinder\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use FriendsOfBotble\ElFinder\ElFinder;

class ElFinderController extends BaseController
{
    public function index(ElFinder $elFinder)
    {
        $this->pageTitle(trans('plugins/elfinder::elfinder.elfinder'));

        $elFinder->registerAssets();

        return view('plugins/elfinder::index');
    }
}
