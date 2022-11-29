<?php

namespace Rikkei\Core\Http\Middleware;

use Closure;
use Lang;
use Session;
use Auth;
use Rikkei\Core\Model\User;

class Locale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $languageList = User::scopeLangArray();
        $user = Auth::user();
        if ($user) {
            if (!$user->language) {
                $lang = array_search(config('app.locale'), $languageList);
                $user->language = (int)$lang;
                $user->save();
            }
            $language = $languageList[$user->language];
            Session::put('locale', $language );

        } else {
            if (!Session::has('locale')) {
                Session::put('locale', config('app.locale'));
            }

            $language = Session::get('locale');
        }

        Lang::setLocale($language);

        return $next($request);
    }
}
