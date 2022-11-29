<?php

namespace Rikkei\Education\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Lang;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Education\Http\Services\EducationRequestService;
use URL;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Education\Http\Requests\EducationRequestsRequest;
use Session;

class EducationRequestController extends Controller
{
    /**
     * construct
     */
    public function __construct(EducationRequestService $service)
    {
        $this->service = $service;
        Breadcrumb::add('Home', URL::to('/'), '<i class="fa fa-dashboard"></i>');
        Breadcrumb::add(Lang::get('education::view.Education.Education request'));
    }

    /**
     * Show list education request
     */
    public function index()
    {
        $view = $this->service->showListEducationRequest();

        return $view;
    }

    /**
     * Show list education request for hr
     */
    public function hrIndex()
    {
        $view = $this->service->showListEducationRequestForHr();

        return $view;
    }

    /**
     * Create education request
     */
    public function create()
    {
        $view = $this->service->createEducationRequest();

        return $view;
    }

    /**
     * Create education request for Hr
     */
    public function hrCreate()
    {
        $view = $this->service->createEducationRequestForHr();

        return $view;
    }

    /**
     * Store education request
     */
    public function store(EducationRequestsRequest $request)
    {
        $store = $this->service->storeEducationRequest($request);

        return $store;
    }


    /**
     * Store education request for Hr
     */
    public function hrStore(EducationRequestsRequest $request)
    {
        $store = $this->service->storeEducationRequestForHr($request);

        return $store;
    }

    /**
     * Edit education request
     */
    public function edit($id)
    {
        $edit = $this->service->editEducationRequest($id);

        return $edit;
    }

    /**
     * Edit education request for hr
     */
    public function hrEdit($id)
    {
        $edit = $this->service->editEducationRequestForHr($id);

        return $edit;
    }

    /**
     * Update education request
     */
    public function update(EducationRequestsRequest $request, $id)
    {
        $update = $this->service->updateEducationRequest($request, $id);

        return $update;
    }


    /**
     * Update education request for Hr
     */
    public function hrUpdate(EducationRequestsRequest $request, $id)
    {
        $update = $this->service->updateEducationRequestForHr($request, $id);

        return $update;
    }

    /**
     * Export education request
     */
    public function export() {
        return $this->service->exportEducationRequest();
    }

    public function getTagAjax()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }

        return response()->json(
            $this->service->getTagAjax([
                'page' => Input::get('page'),
                'query' => trim(Input::get('q'), ' '),
            ])
        );
    }

    public function getTitleAjax()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }

        return response()->json(
            $this->service->getTitleAjax([
                'page' => Input::get('page'),
                'query' => trim(Input::get('q'), ' '),
            ])
        );
    }

    public function getPersonAssignedAjax()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }

        return response()->json(
            $this->service->getPersonAssignedAjax([
                'page' => Input::get('page'),
                'query' => Input::get('q'),
                'employee_branch' => Input::get('employee_branch'),
            ])
        );
    }

    public function getCourseAjax()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }

        return response()->json(
            $this->service->getCourseAjax([
                'page' => Input::get('page'),
                'query' => trim(Input::get('q'), ' '),
            ])
        );
    }
}
