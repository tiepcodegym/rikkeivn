<?php

namespace Rikkei\News\Http\Controllers;

use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Rikkei\News\Model\Category;
use Illuminate\Support\Facades\Session;

class ManageCategoryController extends Controller
{
    /**
     * after construct
     */
    public function _construct() {
        Menu::setActive('admin', 'news');
        Breadcrumb::add('News', URL::route('core::home'));
    }

    /**
     * list post
     */
    public function index()
    {
        return view('news::manage.category.index', [
            'collectionModel' => Category::getGridData(),
            'titleHeadPage' => Lang::get('news::view.List category'),
            'optionStatus' => Category::getAllStatus()
        ]);
    }

    /**
     * create post
     */
    public function create()
    {
        Breadcrumb::add('Category', URL::route('news::manage.category.index'));
        Breadcrumb::add('Create category');

        return view('news::manage.category.edit', [
            'categoryItem' => new Category(),
            'titleHeadPage' => Lang::get('news::view.Create category'),
            'optionStatus' => Category::getAllStatus(),
            'parentMenu' => Category::getAllParentActiveCategory()
        ]);
    }

    /**
     * save data
     */
    public function save()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $response = [];
        $id = Input::get('id');
        if ($id) {
            $cate = Category::find($id);
            if (!$cate) {
                $response['error'] = 1;
                $response['message'] = Lang::get('core::message.Not found item');
                return response()->json($response);
            }
        } else {
            $cate = new Category();
        }
        $dataCate = Input::get('cate');
        $allStatus = implode(',',array_keys(Category::getAllStatus()));
        $validator = Validator::make($dataCate, [
            'title' => 'required',
            'title_en' => 'required',
            'status' => 'required|in:' . $allStatus,
            'sort_order' => 'numeric'
        ]);
        if ($validator->fails()) {
            $response['error'] = 1;
            $response['message'] = Lang::get('core::message.Error input data!');
            return response()->json($response);
        }
        $cate->setData($dataCate);
        try {
            $cate->save();
            $response['success'] = 1;
            $response['message'] = Lang::get('core::message.Save success');
            $response['popup'] = 1;
            $response['refresh'] = URL::route('news::manage.category.edit', ['id' => $cate->id]);
            Session::flash(
            'messages', [
                        'success'=> [
                            $response['message']
                        ]
                    ]
            );
            return response()->json($response);
        } catch (Exception $ex) {
            $response['error'] = 1;
            $response['message'] = Lang::get('core::message.Error system, please try later!');
            Log::info($ex);
            return response()->json($response);
        }
    }

    /**
     * edit post
     */
    public function edit($id)
    {
        $cate = Category::find($id);
        if (!$cate) {
            return redirect()->route('news::manage.category.index')->withErrors(
                Lang::get('core::message.Not found item'));
        }
        Breadcrumb::add('Category', URL::route('news::manage.category.index'));
        Breadcrumb::add('Category edit');
        return view('news::manage.category.edit', [
            'categoryItem' => $cate,
            'titleHeadPage' => Lang::get('news::view.Category edit'),
            'optionStatus' => Category::getAllStatus(),
            'parentMenu' => Category::getAllParentActiveCategory()
        ]);
    }
}
