<?php

namespace Rikkei\News\Http\Controllers\Api;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\News\Model\Post;
use Exception;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    /**
     * api get list post
     */
    public function getPosts()
    {
        try {
            return response()->json([
                'code' => 200,
                'message' => '',
                'data' => [
                    'posts' => Post::getPosts()
                ]
            ]);
        } catch (Exception $ex) {
            Log::error($ex);
            return response()->json([
                'code' => 400,
                'message' => '',
                'data' => []
            ], 400);
        }
    }

    /**
     * api get post detail
     */
    public function getPostsDetail($slug)
    {
        try {
            $post = Post::findFollowSlug($slug);
            if (!$post) {
                return response()->json([
                    'code' => 404,
                    'message' => '',
                    'data' => []
                ], 404);
            }
            return response()->json([
                'code' => 200,
                'message' => '',
                'data' => [
                    'post' => $post
                ]
            ]);
        } catch (Exception $ex) {
            Log::error($ex);
            return response()->json([
                'code' => 400,
                'message' => '',
                'data' => []
            ], 400);
        }
    }
}