<?php

namespace Rikkei\Education\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Education\Http\Requests\SettingAddressMailRequest;
use Rikkei\Education\Http\Services\SettingAddressMailService;
use URL;

class SettingAddressMailController extends Controller
{
    protected $settingAddressService;

    public function __construct(SettingAddressMailService $settingAddressService)
    {
        $this->settingAddressService = $settingAddressService;
    }

    public function index()
    {
        Breadcrumb::add('Home', URL::to('/'), '<i class="fa fa-dashboard"></i>');
        Breadcrumb::add(Lang::get('education::view.Set up mail address'));
        $items = $this->settingAddressService->listItem();

        return view('education::address-mail.index', [
            'titleHeadPage' => Lang::get('education::view.Set up mail address'),
            'collectionModel' => $items
        ]);
    }

    public function show($id)
    {
        Breadcrumb::add('Home', URL::to('/'), '<i class="fa fa-dashboard"></i>');
        Breadcrumb::add(Lang::get('education::view.Set up mail address'));
        $item = $this->settingAddressService->find($id);

        if (!$item) {
            return redirect()->route('education::education.settings.branch-mail')->withErrors(Lang::get('team::messages.Not found item.'));
        }

        return view('education::address-mail.update', [
            'titleHeadPage' => Lang::get('education::view.Set up mail address'),
            'collectionModel' => $item
        ]);
    }

    public function update(SettingAddressMailRequest $request, $id)
    {
        $item = $this->settingAddressService->findAddressMail($id);
        $collection = $this->settingAddressService->updateOrInsertAddressMail($item, $request);
        if ($collection) {
            Session::flash(
                'messages', [
                    'success'=> [
                        Lang::get('education::view.Update success')
                    ]
                ]
            );

            return redirect()->route('education::education.settings.branch-mail');
        }

        return redirect()->route('education::education.settings.branch-mail')->withErrors(Lang::get('team::messages.Not found item.'));
    }
}
