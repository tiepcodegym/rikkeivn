<?php

namespace Rikkei\Education\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Education\Http\Services\SettingEducationService;
use Rikkei\Education\Http\Requests\SettingEducationRequest;
use Rikkei\Education\Http\Requests\UpdateSettingEducationRequest;
use Rikkei\Education\Model\SettingEducation;
use URL;

class SettingEducationController extends Controller
{
    protected $service;

    public function __construct(SettingEducationService $service)
    {
        $this->service = $service;
    }

    /**
     * list post
     */
    public function index()
    {
        Breadcrumb::add('Home', URL::to('/'), '<i class="fa fa-dashboard"></i>');
        Breadcrumb::add(Lang::get('education::view.List setting education'));
        $items = $this->service->listItem();

        return view('education::setting-education.index', [
            'titleHeadPage' => Lang::get('education::view.List setting education'),
            'collectionModel' => $items
        ]);
    }

    /**
     *Create setting education
     */
    public function create()
    {
        Breadcrumb::add('Home', URL::to('/'), '<i class="fa fa-dashboard"></i>');
        Breadcrumb::add(Lang::get('education::view.Education.Create.Create Education'));
        $item = new SettingEducation();

        return view('education::setting-education.create', [
            'collectionModel' => $item,
            'isShow' => true
        ]);
    }

    /**
     * Save setting education
     */
    public function store(SettingEducationRequest $request)
    {
        $response = $this->service->create($request);
        Session::flash(
            'messages', [
                'success'=> [
                    Lang::get('education::view.Create success')
                ]
            ]
        );

        return redirect()->route('education::education.settings.types.index');
    }

    /**
     * Show setting education
     */
    public function show($id)
    {
        Breadcrumb::add('Home', URL::to('/'), '<i class="fa fa-dashboard"></i>');
        Breadcrumb::add(Lang::get('education::view.Education edit education'));
        $item = $this->service->find($id);
        if (!$item) {
            return redirect()->route('education::education.settings.types.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }

        return view('education::setting-education.create', [
            'titleHeadPage' => Lang::get('education::view.List setting education'),
            'collectionModel' => $item,
            'isShow' => true
        ]);
    }

    /**
     * Show setting education
     */
    public function showDetail($id)
    {
        Breadcrumb::add('Home', URL::to('/'), '<i class="fa fa-dashboard"></i>');
        Breadcrumb::add(Lang::get('education::view.Education edit education'));
        $item = $this->service->find($id);
        if (!$item) {
            return redirect()->route('education::education.settings.types.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }

        return view('education::setting-education.show', [
            'titleHeadPage' => Lang::get('education::view.List setting education'),
            'collectionModel' => $item
        ]);
    }

    /**
     * update setting education
     */
    public function update(UpdateSettingEducationRequest $request, $id)
    {
        $item = $this->service->find($id);
        if (!$item) {
            return redirect()->route('education::education.settings.types.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        $this->service->update($item, $request);
        Session::flash(
            'messages', [
                'success'=> [
                    Lang::get('education::view.Update success')
                ]
            ]
        );

        return redirect()->route('education::education.settings.types.index');
    }

    /**
     * delete setting education
     */
    public function delete($id)
    {
        $item = $this->service->find($id);
        if (!$item) {
            return redirect()->route('education::education.settings.types.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        $isCheck = $this->service->checkCodeEducation($id);
        if ($isCheck) {
            return redirect()->route('education::education.settings.types.index')->withErrors(Lang::get('education::view.Education.Error system'));
        }
        $this->service->delete($item);
        Session::flash(
            'messages', [
                'success'=> [
                    Lang::get('education::view.Delete success')
                ]
            ]
        );

        return redirect()->route('education::education.settings.types.index');
    }

    public function checkExitCodeEducation($id)
    {
        $isCheck = $this->service->checkCodeEducation($id);

        if ($isCheck) {
            return response()->json([
                'status' => true
            ]);
        }

        return response()->json([
            'status' => false
        ]);
    }
}
