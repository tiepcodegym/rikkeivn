<?php


namespace Rikkei\HomeMessage\Http\Controllers;


use Illuminate\Routing\Controller as BaseController;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Rikkei\Core\View\Menu;
use Rikkei\HomeMessage\Http\Request\HomeMessageGroupRequest;
use Rikkei\HomeMessage\Model\HomeMessage;
use Rikkei\HomeMessage\Model\HomeMessageGroup;
use Rikkei\Team\View\Config;
use Session;

class HomeMessageGroupController extends BaseController
{
    protected $lang;

    public function __construct()
    {
        $this->lang = Session::get('locale');
        Menu::setFlagActive('group');
    }

    public function getAll()
    {
        $collection = HomeMessageGroup::makeInstance();
        $pager = Config::getPagerData(null, ['order' => "priority", 'dir' => 'ASC']);
        $collection = $collection->orderBy($pager['order'], $pager['dir']);
        $collection = HomeMessageGroup::pagerCollection($collection, $pager['limit'], $pager['page']);
        return view('HomeMessage::group.index', compact('collection'));
    }

    public function insert(HomeMessageGroupRequest $request)
    {
        $dataRequest = $request->only([
            'txt_group_name_vi',
            'txt_group_name_en',
            'txt_group_name_jp',
            'txt_priority']);
        try {
            HomeMessageGroup::makeInstance()
                ->insert([
                    'name_vi' => $dataRequest['txt_group_name_vi'],
                    'name_en' => $dataRequest['txt_group_name_en'],
                    'name_jp' => $dataRequest['txt_group_name_jp'],
                    'priority' => $dataRequest['txt_priority'],
                    'created_id' => Auth::user()->employee_id,
                ]);
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 500);
        }
        return response()->json([], 200);
    }

    /**
     * @param int $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function single($id = 0)
    {
        if ($id == 0) {
            $collection = new HomeMessageGroup();
        } else {
            $collection = HomeMessageGroup::findOrFail($id);
        }
        //load list branch
        $allBranch = (new HomeMessage())->getAllBranch();
        return view('HomeMessage::group.single', compact('collection', 'allBranch'));
    }


    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(HomeMessageGroupRequest $request, $id)
    {
        $dataRequest = $request->only(
            [
                'txt_group_name_vi',
                'txt_group_name_en',
                'txt_group_name_jp',
                'txt_priority']
        );
        try {
            HomeMessageGroup::find($id)
                ->update([
                    'name_vi' => $dataRequest['txt_group_name_vi'],
                    'name_en' => $dataRequest['txt_group_name_en'],
                    'name_jp' => $dataRequest['txt_group_name_jp'],
                    'priority' => $dataRequest['txt_priority'],
                    'created_id' => Auth::user()->employee_id,
                ]);
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 500);
        }
        return response()->json([], 200);
    }

    /**
     * Delete home message group
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        try {
            $homeMessageInfo = HomeMessageGroup::find($id);
            if (!$homeMessageInfo) {
                throw new \Exception(trans('HomeMessage::message.Record does not exist'), 422);
            }
            $homeMessageInfo->delete();
        } catch (\Exception $exception) {
            $code = $exception->getCode();
            $code = !in_array($code, [422, 400]) ? 500 : $code;
            return response()->json($exception->getMessage(), $code);
        }
        return response()->json([]);
    }
}
