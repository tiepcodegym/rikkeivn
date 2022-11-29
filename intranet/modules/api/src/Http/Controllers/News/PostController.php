<?php

namespace Rikkei\Api\Http\Controllers\News;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\News\Model\Category;
use Rikkei\News\Model\Post;
use Rikkei\News\Model\PostCategory;
use Rikkei\Team\View\Config;

class PostController extends Controller
{
    /**
     * List post with ID >= idgte
     * @param null $idgte
     * @return array
     */
    public function listPost($idgte = null)
    {
        try {
            $pager = Config::getPagerData();
            $bodyData = $this->getBodyData();
            $validator = Validator::make($bodyData, [
                'created_from' => 'date_format:Y-m-d',
                'updated_from'=> 'date_format:Y-m-d H:i' 
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => trans('api::message.Error input data!'),
                    'errors' => $validator->errors(),
                ]);
            }
            $page = isset($bodyData['page']) ? $bodyData['page'] : $pager['page'];
            $orderBy = isset($bodyData['order']) ? $bodyData['order'] : $pager['order'];
            $dir = isset($bodyData['dir']) ? $bodyData['dir'] : $pager['dir'];
            $tablePost = Post::getTableName();
            $tableCate = Category::getTableName();
            $tablePostCate = PostCategory::getTableName();

            $collection = Post::select(
                'blog_posts.id',
                'blog_posts.title',
                'blog_posts.slug',
                'blog_posts.status',
                'blog_posts.image',
                'blog_posts.is_set_comment',
                'blog_posts.is_video',
                'blog_posts.youtube_link',
                'blog_posts.youtube_id',
                'blog_posts.desc',
                'blog_posts.short_desc',
                'blog_posts.created_at',
                'blog_posts.tags',
                'blog_posts.author',
                DB::raw("GROUP_CONCAT(DISTINCT {$tableCate}.title SEPARATOR ',') AS cat_title")
            )->leftjoin($tablePostCate, $tablePostCate . '.post_id', '=', $tablePost . '.id')
                ->leftjoin($tableCate, $tablePostCate . '.cat_id', '=', $tableCate . '.id')
                ->whereNull('blog_posts.deleted_at')
                ->where('blog_posts.status', Category::STATUS_ENABLE);

            if (is_numeric($idgte)) {
                $collection = $collection->where('blog_posts.id', '>=', (int)$idgte);
            }

            if (isset($bodyData['category']) && $bodyData['category']) {
                $collection->addSelect("{$tableCate}.sort_order");
                $collection->where($tableCate . '.slug', $bodyData['category']);
            }

            if (isset($bodyData['tags']) && $bodyData['tags'] && is_array($bodyData['tags'])) {
                foreach($bodyData['tags'] as $tag) {
                    $collection->whereRaw("FIND_IN_SET('{$tag}', {$tablePost}.tags)");
                }
            }
            
            if (isset($bodyData['not_include_tags']) && $bodyData['not_include_tags'] && is_array($bodyData['not_include_tags'])) {
                foreach($bodyData['not_include_tags'] as $notTag) {
                    $collection->where(function ($query) use ($notTag, $tablePost) {
                        $query->whereNull("{$tablePost}.tags")
                            ->orWhereRaw("FIND_IN_SET('{$notTag}', {$tablePost}.tags) = 0");
                    });
                }
            }
            if (isset($bodyData['created_from']) && $bodyData['created_from']) {
                $collection->whereDate($tablePost.'.created_at', '>=', $bodyData['created_from']);
            }

            if (isset($bodyData['updated_from']) && $bodyData['updated_from']) {
                $collection->where($tablePost.'.updated_at', '>=', $bodyData['updated_from']);
            }

            if ($orderBy && $dir) {
                $collection->orderBy($orderBy, $dir);
            } else {
                $collection->orderBy('blog_posts.public_at', 'desc')
                    ->orderBy('blog_posts.created_at', 'desc');
            }
            $collection->groupBy('blog_posts.id');

            if (isset($bodyData['limit']) && is_numeric($bodyData['limit'])) {
                Post::pagerCollection($collection, $bodyData['limit'], $page);
            } else {
                $collection = collect(['data' => $collection->get()]);
            }

            return [
                'success' => 1,
                'data' => $collection
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => Post::getInstance()->errorMessage($ex)
            ];
        }
    }

    public function detailPost($id)
    {
        try {
            $collection = Post::select(
                'blog_posts.id',
                'blog_posts.title',
                'blog_posts.slug',
                'blog_posts.status',
                'blog_posts.image',
                'blog_posts.is_set_comment',
                'blog_posts.is_video',
                'blog_posts.youtube_link',
                'blog_posts.youtube_id',
                'blog_posts.desc',
                'blog_posts.tags',
                'blog_posts.short_desc',
                'blog_posts.created_at',
                'employees.name'
            )
                ->join('employees', 'employees.id', '=', 'blog_posts.created_by')
                ->whereNull('blog_posts.deleted_at')
                ->where('blog_posts.status', Category::STATUS_ENABLE)
                ->where('blog_posts.id', '=', $id)
                ->first();

            return [
                'success' => 1,
                'data' => $collection
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => Post::getInstance()->errorMessage($ex)
            ];
        }
    }

    public function getCategories(Request $request)
    {
        $params = $request->all();
        $rules = [
            'category_id' => 'exists:blog_categories,id',
            'updated_from' => 'date_format:Y-m-d H:i',
        ];
        $validator = Validator::make($params, $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => trans('api::message.Error input data!'),
                'errors' => $validator->errors(),
            ]);
        }
        try {
            $response = Category::getCategoriesApi($params);
            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => $ex->getMessage()
            ];
        }
    }

    /**
     * API get data posts from intranet
     */
    public function getDataPosts()
    {
        try {
            $postInstance = Post::getInstance();
            $data = $postInstance->getDataPosts();

            return [
                'success' => 1,
                'data' => $postInstance->formatDataPosts($data),
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => $ex->getMessage()
            ];
        }
    }
}
