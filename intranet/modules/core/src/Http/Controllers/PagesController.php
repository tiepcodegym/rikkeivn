<?php

namespace Rikkei\Core\Http\Controllers;

use Auth;
use Illuminate\Support\Facades\Session;
use Rikkei\News\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Rikkei\Core\Model\User;
use Illuminate\Support\Facades\Input;

class PagesController extends Controller
{
    /**
     * Display home page
     *
     * @return \Illuminate\Http\Response
     */
    public function home()
    {
        if (Auth::guest()) {
            $email = Input::get('email');
            if($email){
                $account=User::where('email',$email)->first();
                Auth::login($account);
                return redirect('/');
            }
            $errors = Session::get('errors', new \Illuminate\Support\MessageBag);
            if ($errors && count($errors) > 0) {
                return view('errors.general');
            }
            return view('core::welcome');
        }
        $post = new PostController();
        return $post->index();
    }

    /**
     * Set language
     *
     * @param Request $request
     */
    public function postLang(Request $request)
    {
        $languageList = User::scopeLangArray();
        $language = array_search($request->input('lang'), $languageList);
        $user = Auth::user();
        $user->language = (int)$language;
        $user->save();

        Session::set('locale', $request->input('lang'));
    }
}
