<?php

namespace Rikkei\News\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Core\View\Menu;
use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Rikkei\Magazine\Model\Magazine;
use Rikkei\News\Model\Post;
use Illuminate\Support\Facades\Input;
use Rikkei\News\Model\Category;
use Rikkei\News\Model\LikeManage;
use Rikkei\News\Model\PostAttach;
use Rikkei\News\Model\PostComment;
use Rikkei\News\Model\ViewManage;
use Rikkei\News\View\ViewNews;
use Rikkei\News\View\ViewPoster;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Resource\View\View;
use Rikkei\Core\View\CacheBase;
use Exception;
use Illuminate\Support\Facades\Log;
use Rikkei\News\Model\BlogMeta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PostController extends Controller
{
    //CONST
    const IS_ALL_VIDEO = true;
    const SLUG_CATEGORY_TRAINING = 'dao-tao';

    /**
     * view home page
     */
    public function index($slug = null)
    {
        $userInfo = Auth::user();
        $searchParam = Input::get('search');
        $headingTitle = Lang::get('news::view.News and event');
        $titleHeadPage = Lang::get('news::view.News Internal');
        $allParams = Input::get();
        // view follow category
        $isCategory = false;
        $isYume = false;
        $isHashTag = false;
        $activeMenu = 'no-active';

        $collectionMagazine = null;
        $postsMiss = null;
        $postSlide = null;
        $topMember = null;
        $videos = null;

        if (isset($allParams['page']) && $allParams['page'] == 1) { //=> homepage
            unset($allParams['page']);
        }
        if ($slug || $allParams) { // view blog - category or search
            Menu::setFlagActive(1);
            $isHome = false;
        } else { // view home page => load cache
            $isHome = true;
        }

        if ($slug) {
            if ($slug == Category::SLUG_YUME) {

                return redirect()->route('magazine::list', [
                    'search' => $searchParam
                ]);
            }
            $isCategory = true;
            $category = Category::findFollowSlug($slug);
            if ($category) {
                $headingTitle = $category->title;
                $titleHeadPage = $category->title . ' - ' . $titleHeadPage;
                if (count($category->parent) != 0 ) {
                    $activeMenu = $category->parent->slug;
                } else {
                    $activeMenu = $category->slug;
                }
            }
        }

        $cacheExpire = Post::CACHE_EXPIRE;
        // Top post in 30 days
        $topPost =  Cache::get(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_TOP_POST);
        if (!$topPost) {
            $topPost = Post::getTopPost();
            Cache::put(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_TOP_POST, $topPost, $cacheExpire);
        }

        //Get Poster
        $posters =  Cache::get(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_POSTER);
        if (!$posters) {
            $posters = ViewPoster::getPosterNews();
            Cache::put(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_POSTER, $posters, $cacheExpire);
        }

        if ($slug == Category::SLUG_UNREAD_POST) {
            // Bai viet chua doc
            $collection = Post::getNotReadPost($userInfo, ['cate_slug' => $slug]);
            $headingTitle = trans('news::view.Unread posts');
            $titleHeadPage = $headingTitle . ' - ' . $titleHeadPage;
        } else if ($slug == Category::SLUG_HASHTAG) {
            $collection = Post::getPostsByTag($searchParam);
            $headingTitle = trans('news::view.Search By Tag');
            $titleHeadPage = $headingTitle . ' - ' . $searchParam;
            $isHashTag = true;
        }  else if ($slug == Category::SLUG_VIDEO) {
            $collection = Post::getPostVideos(self::IS_ALL_VIDEO);
            $headingTitle = trans('news::view.Videos');
            $titleHeadPage = $headingTitle . ' - ' . $searchParam;
            $isHashTag = true;
        } else if ($slug == Category::SLUG_AUDIO) {
            $collection = Post::getAllDataPost(self::IS_ALL_VIDEO);
            $headingTitle = trans('news::view.Videos');
            $titleHeadPage = $headingTitle . ' - ' . $searchParam;
            $isHashTag = true;
        } else {
            $collection = null;
            if (!$searchParam) {
                $collection =  Cache::get(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_KEY);
            }
            if (!$collection || !$isHome) {
                $collection = Post::getPosts([
                    'cate_slug' => $slug,
                    'search' => $searchParam,
                ]);
                if (!$searchParam && $isHome) {
                    Cache::put(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_KEY, $collection, $cacheExpire);
                }
            }
            if (!$searchParam) {
                // Posts slide
                $postSlide =  Cache::get(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_SLIDE);
                if (!$postSlide) {
                    $postSlide = Post::getPostsSlide();
                    Cache::put(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_SLIDE, $postSlide, $cacheExpire);
                }

                $videos =  Cache::get(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_VIDEO);
                if (!$videos) {
                    $videos = Post::getPostVideos();
                    Cache::put(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_VIDEO, $videos, $cacheExpire);
                }
                // Magazine YUME
                $collectionMagazine =  Cache::get(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_MAGAZINE);
                if (!$collectionMagazine) {
                    $collectionMagazine = Magazine::getLists(Input::get());
                    Cache::put(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_MAGAZINE, $collectionMagazine, $cacheExpire);
                }

                // Top member
                $topMember =  Cache::get(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_TOP_MEMBER);
                if (!$topMember) {
                    $topMember = Post::getTopMembers();
                    Cache::put(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_TOP_MEMBER, $topMember, $cacheExpire);
                }

                // Bai viet chua doc
//                /$postsMiss = Post::getNotReadPost($userInfo, ['cate_slug' => $slug]);
            }
        }
        return view('news::post.category', [
            'titleHeadPage' => $titleHeadPage,
            'collectionModel' => $collection,
            'searchParams' => $searchParam,
            'activeCategories' => !$isCategory && CacheBase::hasFile(CacheBase::HOME_PAGE, 'categories') ? false : Category::getAllActiveCategoryCollection(),
            'headingTitle' => $headingTitle,
            'isHome' => $isHome,
            'isYume' => $isYume,
            'collectionMagazine' => $collectionMagazine,
            'postsMiss' => $postsMiss,
            'topPost' => $topPost,
            'topMember' => $topMember,
            'isCategory' => $isCategory,
            'postSlide' => $postSlide,
            'videos' => $videos,
            'isHashTag' => $isHashTag,
            'posters' => $posters,
            'activeMenu' => $activeMenu
        ]);
    }

    /**
     * post view detail
     * @param $slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function view($slug)
    {
        $cacheExpire = Post::CACHE_EXPIRE;
        $post =  Cache::get(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_SLUG . '_' . $slug);
        if (!$post) {
            $post = Post::findFollowSlug($slug);
            Cache::put(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_SLUG . '_' . $slug, $post, $cacheExpire);
        }

        if (!$post) {
            return view('core::errors.404');
        }
        $attach = '';
        if ($post->is_video == Post::TYPE_AUDIO) {
            $attach = PostAttach::getFilePost($post->id);
        }

        $userInfo = Auth::user();
        $headingTitle = null;
        $titleHeadPage = Lang::get('news::view.News Internal');
        $page = PostComment::PAGE;
        $comments =  Cache::get(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_COMMENT . '_' . $post->id);
        if (!$comments) {
            $comments = PostComment::getParentCommentByPost($post->id);
            Cache::put(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_COMMENT . '_' . $post->id, $comments, $cacheExpire);
        }
        $commentsId = !empty($comments) ? $comments->pluck('id')->toArray() : [];
        $commentsReply =  Cache::get(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_COMMENT_REPLY . '_' . $post->id);
        if (!$commentsReply) {
            $commentsReply = PostComment::getAllReplyComment($commentsId, PostComment::NEW_PER_PAGE, $page, $userInfo)->groupBy('parent_id');
            Cache::put(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_COMMENT_REPLY . '_' . $post->id, $commentsReply, $cacheExpire);
        }
        

        if (!$post) {
            return view('core::errors.404');
        }
        $checkPermission = false;
        if (Permission::getInstance()->isAllow('news::post.approveComment')) {
            $checkPermission = true;
        }
        $activeMenu = null;
        $category =  Cache::get(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_FIRST_CATE_POST . '_' . $post->id);
        if (!$category) {
            $category = $post->getFirstCategory();
            Cache::put(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_FIRST_CATE_POST . '_' . $post->id, $category, $cacheExpire);
        }
        
        if ($category) {
            if (count($category->parent) != 0 ) {
                $activeMenu = $category->parent->slug;
            } else {
                $activeMenu = $category->slug;
            }
            $headingTitle = $category->title;
            $titleHeadPage = $category->title . ' - ' . $titleHeadPage;
        }
        $postRelate =  Cache::get(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_RELATE_POST);
        if (!$postRelate) {
            $postRelate = $post->getRelatePost($category, 6);
            Cache::put(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_RELATE_POST, $postRelate, $cacheExpire);
        }
        
        $arrTags = explode(',', $post->tags);
        $topHashTag =  Cache::get(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_TOP_HASH_TAG);
        if (!$topHashTag) {
            $topHashTag = Post::getTopHashTag();
            Cache::put(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_TOP_HASH_TAG, $topHashTag, $cacheExpire);
        }
        

        // Top post in 30 days
        $topPost =  Cache::get(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_TOP_POST);
        if (!$topPost) {
            $topPost = Post::getTopPost();
            Cache::put(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_TOP_POST, $topPost, $cacheExpire);
        }

        DB::beginTransaction();
        try {
            ViewManage::view($post->id, $userInfo->employee_id);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
        }
        //Get Poster
        $posters =  Cache::get(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_POSTER);
        if (!$posters) {
            $posters = ViewPoster::getPosterNews();
            Cache::put(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_POSTER, $posters, $cacheExpire);
        }
        $videos =  Cache::get(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_VIDEO);
        if (!$videos) {
            $videos = Post::getPostVideos();
            Cache::put(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_VIDEO, $videos, $cacheExpire);
        }
        Menu::setFlagActive(1);
        return view('news::post.detail', [
            'titleHeadPage' => $titleHeadPage,
            'activeCategories' => Category::getAllActiveCategoryCollection(),
            'headingTitle' => $headingTitle,
            'postDetail' => $post,
            'attach' => $attach,
            'searchParams' => null,
            'postRelate' => $postRelate,
            'videos' => $videos,
            'userInfo' => $userInfo,
            'comments' => $comments,
            'commentsReply' => $commentsReply,
            'category' => $category,
            'page' => $page,
            'checkPermission' => $checkPermission,
            'optionTrimWord' => ViewNews::getOptionTrimWord(),
            'arrTags' => $arrTags,
            'topHashTag' => $topHashTag,
            'topPost' => $topPost,
            'posters' => $posters,
            'activeMenu' => $activeMenu
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getMoreComment(Request $request)
    {
        $userInfo = Auth::user();
        $checkPermission = Permission::getInstance()->isAllow('news::post.approveComment');
        if (isset($request->parent_id)) {
            $page = $request->page;
            $comment['id'] = $request->parent_id;
            $replyComment = PostComment::countAllCommentReplyById($request->parent_id);
            $commentsReply = PostComment::getAllReplyComment($comment['id'], PostComment::NEW_PER_PAGE, $page, $userInfo)->groupBy('parent_id');
            $view = view('news::post.include.list_reply_comment', [
                'comment' => $comment,
                'page' => $page,
                'checkPermission' => $checkPermission,
                'userInfo' => $userInfo,
                'optionTrimWord' => ViewNews::getOptionTrimWord(),
                'commentsReply' => $commentsReply,
            ])->render();
            return [
                'data' => $replyComment,
                'view' => $view,
            ];
        } else {
            $page = PostComment::PAGE;
            $comments = PostComment::getParentCommentByPost($request->post_id);
            $commentsId = !empty($comments) ? $comments->pluck('id')->toArray() : [];
            $commentsReply = PostComment::getAllReplyComment($commentsId, PostComment::NEW_PER_PAGE, $page, $userInfo)->groupBy('parent_id');
            $view =  view('news::post.include.list_comment', [
                'comments' => $comments,
                'userInfo' => $userInfo,
                'page' => $page,
                'checkPermission' => $checkPermission,
                'optionTrimWord' => ViewNews::getOptionTrimWord(),
                'commentsReply' => $commentsReply,
            ])->render();
            return  [
                'data' => $view,
                'comments' => $comments,
            ];
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function comment(Request $request)
    {
        $userInfo = Auth::user();
        $request->only('post_id', 'comment', 'parent_id', 'status', 'id');
        $this->validate($request, [
            'post_id' => 'numeric',
            'comment' => 'required',
            'parent_id' => 'numeric'
        ]);
        $data = $request->all();
        $data['user_id'] = $userInfo->employee_id;
        $checkPermission = false;
        //cache
        if (CacheHelper::get(PostComment::CACHE_AUTO_APPROVE_COMMENT)) {
            $value = CacheHelper::get(PostComment::CACHE_AUTO_APPROVE_COMMENT);
        } else {
            $value = CoreConfigData::getAccountToEmail(1, 'auto_approve_comment');
            CacheHelper::put(PostComment::CACHE_AUTO_APPROVE_COMMENT, $value);
        }
        DB::beginTransaction();
        try {
        //if setting auto approve comment
        if ($value == CoreConfigData::AUTO_APPROVE) {
            if ($request->id) {
                $comment = PostComment::where('id', $request->id)->first();
                if ($comment->user_id == $userInfo->employee_id) {
                    $comment->update([
                        'comment' => $request->comment,
                        'edit_comment' => null,
                        'status' => PostComment::STATUS_COMMENT_ACTIVE,
                        'approved_by' => null,
                    ]);
                    Cache::forget(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_COMMENT . '_' . $comment->post_id);
                    Cache::forget(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_COMMENT_REPLY . '_' . $comment->post_id);
                    Cache::forget(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_COUNT_COMMENT_REPLY . '_' . $comment->id);
                } else {
                    //if comment not of the owner
                    return -1;
                }
            } else {
                $data['status'] = PostComment::STATUS_COMMENT_ACTIVE;
                $comment = PostComment::insert($data);
            }
        } else {
            if ($request->id) {
                $comment = PostComment::where('id', $request->id)->first();
                if ($comment->user_id == $userInfo->employee_id) {
                    if (trim($comment->comment) != trim($request->comment)) {
                        if ($comment->status === PostComment::STATUS_COMMENT_ACTIVE) {
                            if (Permission::getInstance()->isAllow('news::post.approveComment')) {
                                $comment->update(['comment' => $request->comment]);
                            } else {
                                $comment->update(['edit_comment' => $request->comment]);
                            }
                        } else {
                            if (is_null($comment->edit_comment)) {
                                $comment->update(['comment' => $request->comment]);
                            } else {
                                $comment->update(['edit_comment' => $request->comment]);
                            }
                        }
                    }
                } else {
                    //if comment not of the owner
                    return -1;
                }
            } else {
                if (Permission::getInstance()->isAllow('news::post.approveComment')) {
                    $data['status'] = PostComment::STATUS_COMMENT_ACTIVE;
                    $checkPermission = true;
                } else {
                    $data['status'] = PostComment::STATUS_COMMENT_NOT_ACTIVE;
                }
                $comment = PostComment::insert($data);
            }
        }
        DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return response()->json([
                'status' => 0,
                'message' => trans('core::message.Error system'),
            ], 500);
        }
        if ($request->parent_id) {
            $view = 'news::post.include.comment_reply_pending';
        } else {
            $view = 'news::post.include.comment_pending';
        }
        return view($view, [
            'comment' => $comment,
            'userInfo' => $userInfo,
            'employee' => Employee::getEmpById($userInfo->employee_id),
            'checkPermission' => $checkPermission,
            'optionTrimWord' => ViewNews::getOptionTrimWord(),
        ]);
    }

    /**
     * @param Request $request
     */
    public function approveComment(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|numeric',
        ]);
        $data['status'] = PostComment::STATUS_COMMENT_ACTIVE;
        $data['approved_by'] = Auth::user()->employee_id;
        $postComment = PostComment::find($request->id);
        if (!is_null($postComment->edit_comment)) {
            $data['comment'] = $postComment->edit_comment;
            $data['edit_comment'] = null;
        }
        PostComment::updateComment($request->id, $data);
        return Response::json(trans('news::message.Approve success'));
    }

    /**
     * post like
     */
    public function like()
    {
        $postId = Input::get('post_id');
        $type = Input::get('type');
        $resultType = LikeManage::getLikeTypeKey($type);
        if ($resultType['type'] == LikeManage::TYPE_POST) {
            $post = Post::find($postId);
            $cmt = true;
        } else {
            $cmt = PostComment::find($postId);
            if (!$cmt) {
                return response()->json([
                    'status' => 0,
                    'message' => trans('core::message.Not found item'),
                ], 500);
            }
            $post = Post::find($cmt->post_id);
        }
        if (!$post) {
            return response()->json([
                'status' => 0,
                'message' => trans('core::message.Not found item'),
            ], 500);
        }
        DB::beginTransaction();
        try {
            $response = [
                'status' => 1,
                $resultType['key'] => [$postId => LikeManage::like($postId, $resultType['type'])],
            ];
            DB::commit();
            return $response;
        } catch (Exception $ex) {
            Log::info($ex);
            DB::rollback();
            return response()->json([
                'status' => 0,
                'message' => trans('core::message.Error system'),
            ], 500);
        }
    }

    /**
     * request get all count of posts
     */
    public function getAllCount()
    {
        $ids = Input::get('ids');
        if (!$ids) {
            return response()->json([
                'status' => 0,
            ], 500);
        }
        $ids = explode('-', $ids);
        if (!$ids || !count($ids)) {
            return response()->json([
                'status' => 0,
            ], 500);
        }
        return array_merge([
            'status' => 1,
        ], BlogMeta::getAllCount($ids, Auth::id()));
    }

    /**
     * view post for guest
     */
    public function postForGuest($render = null)
    {
        $headingTitle = Lang::get('news::view.News and event');
        $titleHeadPage = 'Bản tin Rikkeisoft';
        if ($render != null) {
            $post = Post::getPostFollowRender($render);
            if ($post) {
                if (Auth::check()) {
                    $slug = $post->slug;
                    return redirect()->route('news::post.view', ['slug' => $slug]);
                }
                $postRelate = null;
                return view('news::post.guest-detail', [
                    'titleHeadPage' => $titleHeadPage,
                    'headingTitle' => $headingTitle,
                    'postDetail' => $post
                ]);
            } else {
                return view('errors.404');
            }
        } else {
            return view('errors.404');
        }
    }

    /**
     * view list like of post
     */
    public function getListLike()
    {
        return [
            'status' => 1,
            'data' => LikeManage::getListLike(Input::get('post_id'), Input::get('type')),
        ];
    }

    /**
     * @param Request $request
     */
    public function deleteComment(Request $request)
    {
        $cmt = PostComment::find($request->id);
        if (!$cmt ||
            $cmt->user_id != Permission::getInstance()->getEmployee()->id
        ) {
            return response()->json([
                'status' => 0,
                'message' => trans('core::message.Not found item'),
            ], 500);
        }
        $post = Post::find($cmt->post_id);
        if (!$post) {
            return response()->json([
                'status' => 0,
                'message' => trans('core::message.Not found item'),
            ], 500);
        }
        DB::beginTransaction();
        try {
            $count = $cmt->delete();
            DB::commit();
            return response()->json([
                'status' => 1,
                'count' => $count,
            ]);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return response()->json([
                'status' => 0,
                'message' => trans('core::message.Error system'),
            ], 500);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function publishNews(Request $request)
    {
        $url = Config('services.curl');
        $data = $request->all();

        //Update field `published` in table blog_posts
        Post::where('id', $data['id'])->update(['published' => Post::STATUS_PUBLISHED]);

        //Publish to webvn
        $token = CoreConfigData::getApiToken();
        $data = [
            'data' => $data,
            'token' => $token,
        ];
        $url = $url['server_webvn'].'/news/insert_news';
        return View::postData($data, $url);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function publishNewsRecruitment(Request $request)
    {
        $url = Config('services.curl');
        $data = $request->all();
        $categoryTraining = Category::findFollowSlug(self::SLUG_CATEGORY_TRAINING);

        $response = [];
        DB::beginTransaction();
        try {
            //Update field `published` in table blog_posts
            Post::where('id', $data['id'])->update(['published' => Post::STATUS_PUBLISHED]);
            $attach = PostAttach::getFilePost($data['id']);
            $data['attach'] = $attach;
            $token = CoreConfigData::getApiToken();
            //Publish to webvn
            $data['post']['date'] = !empty($data['post']['public_at']) ? $data['post']['public_at'] : date('Y-m-d H:i:s');
            $data['post']['short_desc'] = !empty($data['post']['short_desc']) ? $data['post']['short_desc'] : str_limit(strip_tags(!empty($data['post']['desc'])), 300);
            $dataSend = $data['post'];
            $dataSend['id_post'] = (int)$data['id'];
            $dataSend['_token'] = $token;
            if (isset($data['category']) && $categoryTraining && in_array($categoryTraining->id, $data['category']['id'])) {
                $dataSend['category'] = $categoryTraining->id;
            }

            $url = $url['server_recruitment'].'/api/admin/news/insert';
            $checkError = View::postData($dataSend, $url);
            DB::commit();
            return $checkError;
        } catch (\Exception $ex) {
            $response['error'] = 'Error system';
            Log::info($ex);
            DB::rollback();
            return response()->json($ex->getMessage(), 402);
        }
    }
}
