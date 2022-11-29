<?php

namespace Rikkei\News\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\News\Model\Post;
use Illuminate\Support\Facades\Lang;
use Illuminate\Http\Request;

class FeaturedArticleController extends Controller
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
        return view('news::manage.featured_article.index', [
            'collectionModel' => Post::getGridDataFeaturedArticle(),
            'titleHeadPage' => Lang::get('news::view.Featured post list'),
            'optionStatus' => Post::getAllStatus(),
            'optionType' => Post::getAllTypePost(),
        ]);
    }

    /**
     * list post
     */
    public function updateImpotentAndSetTop(Request $request)
    {
        $post = Post::whereId($request->id)->first();
        if (isset($request->important)) {
            $post->important = $request->important;
            $post->save();
        }

        if (isset($request->set_top)) {
            $post->set_top = $request->set_top;
            if ($post->is_video) {
                $post->important = "0";
            }
            $post->save();
        }

        Session::flash(
            'messages', [
                'success'=> [
                    Lang::get('test::validate.update_success')
                ]
            ]
        );

        return redirect()->route('news::manage.featured_article.index');
    }
}
