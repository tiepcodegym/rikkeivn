<?php

namespace Rikkei\News\Http\Controllers;

use Carbon\Carbon;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\News\Model\Post;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Session;
use Rikkei\News\Model\PostAttach;
use Rikkei\News\Model\PostCategory;
use Rikkei\News\Model\Category;
use Rikkei\Core\View\CoreImageHelper;
use Illuminate\Support\Facades\Config;
use Rikkei\News\Model\ViewManage;
use Rikkei\Notify\Classes\RkNotify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Rikkei\News\Model\PostSchedule;
use Rikkei\Team\Model\Team;

class ManagePostController extends Controller
{
    /**
     * after construct
     */
    public function _construct() {
        Menu::setActive('admin', 'news');
    }

    /**
     * list post
     */
    public function index()
    {
        $listViewByBranches = ViewManage::getViewByBranch();
        $branch = Team::listPrefixByRegion();
        return view('news::manage.post.index', [
            'collectionModel' => Post::getGridData(),
            'titleHeadPage' => Lang::get('news::view.List post'),
            'optionStatus' => Post::getAllStatus(),
            'optionType' => Post::getAllTypePost(),
            'hanoiView' => $listViewByBranches[$branch[Team::TYPE_REGION_HN]],
            'dnView' => $listViewByBranches[$branch[Team::TYPE_REGION_DN]],
            'hcmView' => $listViewByBranches[$branch[Team::TYPE_REGION_HCM]],
            'japanView' => $listViewByBranches[$branch[Team::TYPE_REGION_JP]],
        ]);
    }

    /**
     * create post
     */
    public function create()
    {
        Breadcrumb::add('Posts', URL::route('news::manage.post.index'));
        Breadcrumb::add('Create post');
        $postItem = new Post();
        $postItem->status = Post::STATUS_ENABLE;
        return view('news::manage.post.edit', [
            'postItem' => $postItem,
            'titleHeadPage' => Lang::get('news::view.Create post'),
            'optionStatus' => Post::getAllStatus(),
            'optionPublic' => Post::getAllPublic(),
            'postCategories' => [],
            'allCategory' => Category::getAllActiveCategory()
        ]);
    }

    /**
     * save data
     */
    public function save(Request $request)
    {
        if (!$request) {
            return redirect('/');
        }
        $id = Input::get('id');
        $data = $request->all();
        $dataPost = $data['post'];
        if (isset($dataPost['tags']) && count($dataPost['tags'])) {
            foreach ($dataPost['tags'] as $key => $tag) {
                $tag = str_replace('#', '', $tag);
                $dataPost['tags'][$key] = '#' . $tag;
            }
        }
        $dataPost['tags'] = implode(',', $dataPost['tags']) . ',';

        unset($dataPost['category']);

        if (isset($id)) {
            $post = Post::find($data['id']);
            if (!$post) {
                $response['error'] = 1;
                $response['message'] = Lang::get('core::message.Not found item');
                return response()->json($response);
            }
            unset($dataPost['public_at']);
        } else {
            $post = new Post();
        }

        $allStatus = implode(',',array_keys(Post::getAllStatus()));
        $validator = Validator::make($dataPost, [
            'title' => 'required',
            'status' => 'required|in:' . $allStatus,
        ]);
        if ($validator->fails()) {
            $response['error'] = 1;
            $response['message'] = Lang::get('core::message.Error input data!');
            return response()->json($response);
        }
        $post->setData($dataPost);
        try {
            $post->save();
            $post_id = $post->id;  
            $postSchedule = PostSchedule::withTrashed()->where('post_id', $post_id)->first();
            if (( $postSchedule && $data['post']['status']) == Post::STATUS_ENABLE ) {
                PostSchedule::withTrashed()->where('post_id', $post_id)->update(['deleted_at' => Carbon::parse($data['schedulePost'])->format('Y-m-d H:i')]);
            } else {
                $publishAt = (empty($data['schedulePost'])) ? null : Carbon::parse($data['schedulePost'])->format('Y-m-d H:i');
                if( !empty($publishAt)){
                    if ($data['schedulePost'] < Carbon::now()) {
                        $messages = [
                            'errors' => [
                                Lang::get('news::message.Invalid Schedule'),
                            ]
                        ];
                        return redirect()->route('news::manage.post.edit', ['id' => $post->id])->with('messages', $messages);
                    }
                    if (!empty($postSchedule) )  {
                        $postSchedule->publish_at = $publishAt;
                        $postSchedule->deleted_at = null;
                        $postSchedule->update();
                    } else {
                        PostSchedule::create([
                            'post_id'    => $post_id,
                            'publish_at' => $data['schedulePost']
                        ]);
                    }
                }     
            }
            
            if ($dataPost['is_video'] == Post::TYPE_AUDIO && isset($data['audio_link'])) {
                PostAttach::uploadFiles($post['id'], $data['audio_link']);
            }
            PostCategory::savePostCategory($post->id, (array) Input::get('category.id'));
            $response['success'] = 1;
            $response['message'] = Lang::get('core::message.Save success');
            if($post->render&&$post->status == Post::STATUS_ENABLE) {
                $response['message'] = $response['message'].trans('news::view.render message');
            }
            $response['popup'] = 1;
            $response['refresh'] = URL::route('news::manage.post.edit', ['id' => $post->id]);
            Session::flash(
            'messages', [
                        'success'=> [
                            $response['message']
                        ]
                    ]
            );
            try {
                if ($dataPost['image']) {
                    CoreImageHelper::getInstance()
                        ->setImage($dataPost['image'])
                        ->resize(Config::get('image.news.size_thumbnail_width'),
                            Config::get('image.news.size_thumbnail_height'));
                    CoreImageHelper::getInstance()
                        ->setImage($dataPost['image'])
                        ->resize(Config::get('image.news.size_detail_width'),
                            Config::get('image.news.size_detail_height'));
                }
            } catch (Exception $ex) {
                Log::info($ex);
            }
            if (!$id && $post->status == Post::STATUS_ENABLE) {
                $dataFirebase = [
                    'category_id' => RkNotify::CATEGORY_NEWS,
                    'news_id' => $post->id,
                    'message' => $dataPost['title'],
                    'is_ot' => false
                ];
                \RkNotify::sendNotification($dataFirebase);
            }
            $messages = [
                'success' => [
                    Lang::get('news::message.Save post success'),
                ]
            ];
            return redirect()->route('news::manage.post.edit', ['id' => $post->id])->with('messages', $messages);
        } catch (Exception $ex) {
            $response['error'] = 1;
            $response['message'] = Lang::get('core::message.Error system, please try later!');
            Log::info($ex);
            return response()->json($response);
        }
    }

    /**
     * deleted file post
     */
    public function deletedFile(Request $request)
    {
        $fileId = PostAttach::deleteFileAttach($request);
        return response()->json($fileId);
    }

    /**
     * edit post
     */
    public function edit($id)
    {
        $post = Post::getGridDataPost($id);
        if (!$post) {
            return redirect()->route('news::manage.post.index')->withErrors(
                Lang::get('core::message.Not found item'));
        }
        Breadcrumb::add('Posts', URL::route('news::manage.post.index'));
        Breadcrumb::add('Post details');
        return view('news::manage.post.edit', [
            'postItem' => $post,
            'titleHeadPage' => Lang::get('news::view.Post edit'),
            'optionStatus' => Post::getAllStatus(),
            'optionPublic' => Post::getAllPublic(),
            'optionType' => Post::getAllTypePost(),
            'postCategories' => PostCategory::getIdsCategoryOfPost($id),
            'allCategory' => Category::getAllActiveCategory()
        ]);
    }
}
