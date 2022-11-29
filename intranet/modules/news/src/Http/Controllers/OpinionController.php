<?php

namespace Rikkei\News\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\News\Model\Opinion;
use Rikkei\News\View\ViewOpinion;
use Rikkei\Core\Http\Controllers\Controller;


class OpinionController extends Controller
{
    private $viewOpinion;

    public function __construct(ViewOpinion $viewOpinion)
    {
        parent::__construct();
        Menu::setActive('admin', 'news');
        $this->viewOpinion = $viewOpinion;
    }

    public function index()
    {
        $collection = $this->viewOpinion->index();
        $listStatus = Opinion::getStatus();
        $listStatusLabel = Opinion::getStatusLabel();
        $employeeFilter = $this->viewOpinion->getFilterEmployee();

        return view('news::opinion.index', [
            'collectionModel' => $collection,
            'listStatus' => $listStatus,
            'filterEmployeeId' => $employeeFilter ? $employeeFilter->id : null,
            'filterEmployeeName' => $employeeFilter ? $employeeFilter->name : null,
            'listStatusLabel' => $listStatusLabel
        ]);
    }


    public function store(Request $request)
    {
        $this->validate($request, [
            'employee_id' => 'required|exists:employees,id',
            'content' => 'required'
        ]);

        $this->viewOpinion->store($request);
        $response = [
            'data' => true,
            'message' => 'success',
            'status' => 200
        ];

        return response()->json($response);
    }

    public function edit($id)
    {
        Breadcrumb::add(trans('news::view.List opinion'), URL::route('news::opinions.index'));
        Breadcrumb::add(trans('news::view.Show opinion'));

        $model = $this->viewOpinion->edit($id);
        $listStatus = Opinion::getStatus();
        $listStatusLabel = Opinion::getStatusLabel();

        return view('news::opinion.edit', [
            'model' => $model,
            'listStatus' => $listStatus,
            'listStatusLabel' => $listStatusLabel
        ]);
    }

    public function update($id, Request $request)
    {
        $this->validate($request, [
            'status' => 'required|in:' . implode(',', array_keys(Opinion::getStatus())),
        ]);

        $this->viewOpinion->update($id, $request);

        return redirect()->route('news::opinions.index')->with('flash_success', Lang::get('core::message.Save success'));
    }

    public function delete($id)
    {
        $this->viewOpinion->delete($id);

        return redirect()->route('news::opinions.index');
    }
}
