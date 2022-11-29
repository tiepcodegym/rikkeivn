<?php

namespace Rikkei\QA\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Rikkei\QA\Model\Category;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Exception;

class CategoryController extends Controller
{
    /**
     * list cate
     */
    public function index()
    {
        return view('qa::category.list');
    }
    
    /**
     * get list of category
     */
    public function getList()
    {
        $active = Input::get('active');
        if ($active == 1 || $active === null) {
            $active = 1;
        } else {
            $active = 0;
        }
        return response()->json([
            'qaMenuLeft' => 'cate',
            'pagerCollection' => Category::getList(['active' => $active])
        ]);
    }
    
    /**
     * save cate
     */
    public function save()
    {
        $id = Input::get('item.id');
        $response = [];
        if ($id) {
            $category = Category::find($id);
            if (!$category) {
                $response['success'] = 0;
                $response['message'] = Lang::get('core::message.Not found item');
                return response()->json($response);
            }
        } else {
            $category = new Category();
        }
        $category->setData(Input::get('item'));
        try {
            $category->save();
            $response['success'] = 1;
            $response['message'] = Lang::get('core::message.Save success');
            return response()->json($response);
        } catch (Exception $ex) {
            Log::info($ex);
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Error system');
            return response()->json($response);
        }
    }
    
    /**
     * get item of category
     */
    public function getItem()
    {
        $response = [];
        $id = Input::get('id');
        if (!$id) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Not found item.');
            return response()->json($response);
        }
        $category = Category::find($id);
        if (!$category) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Not found item.');
            return response()->json($response);
        }
        $response['success'] = 1;
        $response['item'] = [
            'id' => $category->id,
            'name' => $category->name,
            'content' => $category->content,
            'active' => $category->active,
            'public' => $category->public,
        ];
        return response()->json($response);
    }
}