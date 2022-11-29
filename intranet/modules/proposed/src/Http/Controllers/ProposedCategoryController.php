<?php

namespace Rikkei\Proposed\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\View\View;
use Rikkei\Proposed\View\ProposedCategoryPermission;
use Rikkei\Proposed\Model\ProposedCategory;

use Rikkei\Team\View\Permission;

/**
 * Description of ContactController
 *
 * @author ngochv
 */
class ProposedCategoryController extends Controller
{
    /**
     * list proposed category
     * @return [type]
     */
    public function index(Request $request)
    {
        if (!ProposedCategoryPermission::isAllow()) {
            View::viewErrorPermission();
        }

        $proposedCategory = new ProposedCategory();
        return view('proposed::proposed_category.manage_list', [
            'collectionModel' => $proposedCategory->index(),
        ]);
    }

    /**
     * insert proposed category
     * @param  Request $request
     * @return [type]
     */
    public function store(Request $request)
    {
        if (!ProposedCategoryPermission::isAllow()) {
            View::viewErrorPermission();
        }

        if ($validator = static::validator($request)) {
            return redirect()->back()->with('flash_error', $validator);
        }

        $userCurrent = Permission::getInstance()->getEmployee();
        $data = [
            'name_vi' => $request->name['vi'],
            'name_en' => $request->name['en'],
            'name_ja' => $request->name['ja'],
            'created_by' => $userCurrent->id,
            'status' => $request->status,
        ];
        try {
            ProposedCategory::create($data);
            return redirect()->back()->with('flash_success', Lang::get('proposed::message.Add success'));
        } catch (Exception $e) {
            \Log::error($e);
            return redirect()->back()->withErrors($ex->getMessage());
        }

    }

    /**
     * insert proposed category
     * @param  Request $request
     * @return [type]
     */
    public function update(Request $request)
    {
        if (!ProposedCategoryPermission::isAllow()) {
            View::viewErrorPermission();
        }
        if ($validator = static::validator($request, $request->id)) {
            return redirect()->back()->withErrors($validator);
        }

        $userCurrent = Permission::getInstance()->getEmployee();
        $proCate = ProposedCategory::find($request->id);
        if (!$proCate) {
            return redirect()->back()->withErrors(Lang::get('proposed::message.Not found item'));
        }

        if (!empty($proCate->created_by) && $userCurrent->id != $proCate->created_by) {
            return redirect()->back()->withErrors(Lang::get('proposed::message.Not permission'));
        }
        $data = [
            'name_vi' => $request->name['vi'],
            'name_en' => $request->name['en'],
            'name_ja' => $request->name['ja'],
            'created_by' => $userCurrent->id,
            'status' => $request->status,
        ];

        try {
            $proCate->update($data);
            return redirect()->back()->with('flash_success', Lang::get('proposed::message.Update success'));
        } catch (Exception $ex) {
            \Log::error($ex);
            return redirect()->back()->withErrors($ex->getMessage());
        }

    }

    public static function validator($request, $id = null)
    {
        $arrName = $request->name;
        if ($arrName['vi'] == '' &&
            $arrName['en'] == '' &&
            $arrName['ja'] == '') {
                return Lang::get('proposed::message.All name category not empty');
            }
        $proCate = ProposedCategory::all();
        $cate = '';
        if ($id) {
            $cate = ProposedCategory::find($id);
        }
        if ($proCate) {
            foreach ($proCate as $item) {
                if ($cate && $cate->id == $item->id) {
                    continue;
                }
                if ((!empty($item->name_vi) && $item->name_vi === $arrName['vi']) ||
                    (!empty($item->name_en) && $item->name_en === $arrName['en']) ||
                    (!empty($item->name_ja) && $item->name_ja === $arrName['ja'])) {
                    return Lang::get('proposed::message.Name category exist');
                }
            }
        }

        return false;
    }

    /**
     * delete proposed category
     * @param  [int] $id
     * @return [type]
     */
    public function delete($id)
    {
        if (!ProposedCategoryPermission::isAllow()) {
            View::viewErrorPermission();
        }
        $userCurrent = Permission::getInstance()->getEmployee();
        $proCate = ProposedCategory::find($id);
        if (!$proCate) {
            return redirect()->back()->withErrors(Lang::get('proposed::message.Not found item'));
        }

        if (!empty($proCate->created_by) && $userCurrent->id != $proCate->created_by) {
            return redirect()->back()->withErrors(Lang::get('proposed::message.Not permission'));
        }
        $proCate->delete();
        return redirect()->route('proposed::manage-proposed.category.index')
            ->with('flash_success', Lang::get('proposed::message.Delete success'));
    }
}
