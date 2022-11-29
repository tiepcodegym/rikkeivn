<?php


namespace Rikkei\HomeMessage\Http\Controllers;


use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Rikkei\Core\View\Menu;
use Rikkei\HomeMessage\Helper\Constant;
use Rikkei\HomeMessage\Helper\RikkeiAppBackendApiConnect;
use Rikkei\HomeMessage\Model\HomeMessageBanner;
use Rikkei\HomeMessage\View\FileUploader;
use Rikkei\News\Model\Post;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\TeamList;
use Rikkei\Test\Models\Test;

class HomeMessageBannerController extends BaseController
{
    public function __construct()
    {
        Menu::setFlagActive('banner');
    }

    public function getAll()
    {
        $collection = HomeMessageBanner::makeInstance();
        $pager = Config::getPagerData(null, ['order' => "id", 'dir' => 'DESC', 'limit' => Constant::PAGINATE_DEFAULT]);
        $collection = $collection->orderBy($pager['order'], $pager['dir']);
        $collection = HomeMessageBanner::pagerCollection($collection, $pager['limit'], $pager['page']);
        return view('HomeMessage::banner.index', compact('collection'));
    }

    public function insert(Request $request)
    {
        $dataRequest = $this->cleanUpDataRequest($request);
        $validate = $this->validate($dataRequest);
        if ($validate->fails()) {
            return response()->json($validate->errors(), 422);
        }
        try {
            DB::beginTransaction();
            $dataRequest = $this->imageUpload($dataRequest, $request);
            $homeMessageBanner = HomeMessageBanner::create($dataRequest);
            $homeMessageBanner->teams()->sync($dataRequest['branches']);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollback();
            return response()->json(['message' => $exception->getMessage()], 500);
        }
        return response()->json([], 200);
    }

    /**
     * @param int $id
     * @return Factory|View
     * @throws GuzzleException
     */
    public function single($id = 0)
    {
        $uri = '/api/v1/admin/shake-rule/action/get-all-by-current-date';
        $urlDonate = '/api/v1/donates';
        if ($id == 0) {
            $collection = new HomeMessageBanner();
        } else {
            $collection = HomeMessageBanner::findOrFail($id);
            $uri .= '?shake_rule_id=' . $collection->event_id;
        }
        $collection->load('teams');

        $events = RikkeiAppBackendApiConnect::makeInstance()
            ->setUrlRequest($uri)->send('GET');

        $donates = RikkeiAppBackendApiConnect::makeInstance()->setUrlRequest($urlDonate)->send('GET');
        $data = [
            'collection' => $collection,
            'branches' => $teamsOption = TeamList::toOption(null, true, false),
            'events' => $events['success'] ? $events['data'] : [],
            'tests' => Test::has('testAssignees')->orderBy('name', 'ASC')->get(['id', 'name']),
            'donates' => $donates['success'] ? $donates['data'] : []
        ];
        return view('HomeMessage::banner.single', $data);
    }

    /**
     * @param array $dataRequest
     * @param null $id
     * @return mixed
     */
    protected function validate($dataRequest = [], $id = null)
    {
        $rules = [
            'display_name' => 'required',
            'begin_at' => 'required|date',
            'end_at' => 'required|date',
        ];

        if ($id === null) {
            $rules['image'] = 'required';
        }

        return Validator::make($dataRequest, $rules, [
            'display_name.required' => trans('HomeMessage::message.The display_name field is required'),
            'image.required' => trans('HomeMessage::message.The image field is required'),
            'begin_at.required' => trans('HomeMessage::message.The begin_at field is required'),
            'end_at.required' => trans('HomeMessage::message.The end_at field is required'),
            'begin_at.date' => trans('HomeMessage::message.The begin_at field is not valid date'),
            'end_at.date' => trans('HomeMessage::message.The end_at field is not valid date'),
        ]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $dataRequest = $this->cleanUpDataRequest($request);
        $validate = $this->validate($dataRequest, $id);
        if ($validate->fails()) {
            return response()->json($validate->errors(), 422);
        }
        try {
            DB::beginTransaction();
            $homeMessageBanner = HomeMessageBanner::find($id);
            $dataRequest = $this->imageUpload($dataRequest, $request, $homeMessageBanner);
            $homeMessageBanner->update($dataRequest);
            $homeMessageBanner->teams()->sync($dataRequest['branches']);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollback();
            return response()->json(['message' => $exception->getMessage()], 500);
        }
        return response()->json([], 200);
    }

    /**
     * Delete home message group
     * @param $id
     * @return JsonResponse
     */
    public function delete($id)
    {
        try {
            DB::beginTransaction();
            $homeMessageBanner = HomeMessageBanner::find($id);
            if (!$homeMessageBanner) {
                throw new \Exception(trans('HomeMessage::message.Record does not exist'), 422);
            }
            $homeMessageBanner->delete();
            DB::commit();
        } catch (\Exception $exception) {
            $code = $exception->getCode();
            $code = !in_array($code, [422, 400]) ? 500 : $code;
            DB::rollback();
            return response()->json($exception->getMessage(), $code);
        }
        return response()->json([]);
    }


    public function findBlog(Request $request)
    {
        try {
            $delimiter = config('app.url') . Constant::BLOG_SLUG;
            $slug = explode($delimiter, $request->get('url'));
            if (empty($slug[1])) {
                throw new \Exception(trans('HomeMessage::message.Record does not exist'), 422);
            }
            $blog = Post::where('slug', $slug[1])->first();
            if (empty($blog)) {
                throw new \Exception(trans('HomeMessage::message.Record does not exist'), 422);
            }
            return response()->json($blog);
        } catch (\Exception $exception) {
            $code = $exception->getCode();
            $code = !in_array($code, [422, 400]) ? 500 : $code;
            return response()->json($exception->getMessage(), $code);
        }
    }


    /**
     * clean input request
     * @param $request
     * @return array
     */
    private function cleanUpDataRequest($request)
    {
        $dataRequest = $request->only('display_name', 'image', 'link', 'begin_at', 'end_at', 'branches', 'event_id',
            'type', 'action_id', 'status', 'test_id', 'donate_id', 'gender_target');
        $dataRequest['begin_at'] = Carbon::parse($dataRequest['begin_at'])->format(Constant::dateTimeFormat());
        $dataRequest['end_at'] = Carbon::parse($dataRequest['end_at'])->format(Constant::dateTimeFormat());
        $dataRequest['branches'] = isset($dataRequest['branches']) ? $dataRequest['branches'] : [];
        if ($dataRequest['type'] == Constant::HOME_MESSAGE_BANNER_TYPE_SHAKE) {
            $dataRequest['action_id'] = $dataRequest['event_id'];
        }
        if ($dataRequest['type'] == Constant::HOME_MESSAGE_BANNER_TYPE_TEST) {
            $dataRequest['action_id'] = $dataRequest['test_id'];
        }
        if ($dataRequest['type'] == Constant::HOME_MESSAGE_BANNER_TYPE_DONATE) {
            $dataRequest['action_id'] = $dataRequest['donate_id'];
        }
        if ($dataRequest['type'] == Constant::HOME_MESSAGE_BANNER_TYPE_NEWS) {
            $delimiter = config('app.url') . Constant::BLOG_SLUG;
            $slug = explode($delimiter, $dataRequest['link']);
            if (isset($slug[1])) {
                $blog = Post::where('slug', $slug[1])->first();
                $dataRequest['action_id'] = $blog->id;
            } else {
                $dataRequest['action_id'] = "";
            }
        }
        if ($dataRequest['type'] == Constant::HOME_MESSAGE_BANNER_TYPE_GRATEFUL) {
            $dataRequest['link'] = config('api.rikkei_app_backend')['grateful'];
        }
        if ($dataRequest['gender_target'] === '') {
            $dataRequest['gender_target'] = null;
        }
        return $dataRequest;
    }

    /**
     * upload image
     * @param $dataRequest
     * @param $request
     * @param $homeMessageBanner = null
     * @return array
     * @throws Exception
     */
    private function imageUpload($dataRequest, $request, $homeMessageBanner = null)
    {
        if ($request->hasFile('image')) {
            $image = new FileUploader('image', '/home-message/banners/', []);
            $image = $image->upload();
            $dataRequest['image'] = $image['files'][0]['file'];
            return $dataRequest;
        }
        if (!empty($homeMessageBanner)) {
            $dataRequest['image'] = $homeMessageBanner->image;
        }
        return $dataRequest;
    }
}
