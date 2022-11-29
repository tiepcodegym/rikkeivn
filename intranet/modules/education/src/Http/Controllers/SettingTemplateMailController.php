<?php

namespace Rikkei\Education\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Education\Http\Services\SettingTemplateMailService;
use Rikkei\Education\Http\Requests\TemplateMailRequest;
use Rikkei\Education\Model\SettingTemplateMail;
use URL;

class SettingTemplateMailController extends Controller
{
    protected $settingTemplateService;

    public function __construct(SettingTemplateMailService $settingTemplateService)
    {
        $this->settingTemplateService = $settingTemplateService;
        Breadcrumb::add('Home', URL::to('/'), '<i class="fa fa-dashboard"></i>');
        Breadcrumb::add(Lang::get('education::view.List template mail'));
    }

    public function index($type = null)
    {
        $typesView = SettingTemplateMail::labelTypeTemplateFull();

        if (!$type || !in_array($type, array_keys($typesView))) {
            $type = SettingTemplateMail::TEMPLATE_INVITE;
        }

        $collection = $this->settingTemplateService->getItem($type);

        return view('education::template.index', [
            'titleHeadPage' => Lang::get('education::view.List template mail'),
            'typeViewMain' => $type,
            'typesView' => $typesView,
            'collection' => $collection
        ]);
    }

    public function updateTemplate(TemplateMailRequest $request)
    {
        $collection = $this->settingTemplateService->updateTemplateMail($request);
        if ($collection) {
            Session::flash(
                'messages', [
                    'success'=> [
                        Lang::get('education::view.Update success')
                    ]
                ]
            );

            return redirect()->back();
        }

        return redirect()->route('education::education.settings.index-template')->withErrors(Lang::get('team::messages.Not found item.'));
    }
}
