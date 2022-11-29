<?php

namespace Rikkei\Core\View;

use Illuminate\Support\Facades\Session;

class CoreLang
{
    const DEFAULT_LANG = 'vi';

    public static function allLang()
    {
        return [
            'vi' => trans('core::view.Vietnamese'),
            'en' => trans('core::view.English'),
            'jp' => trans('core::view.Japanese'),
        ];
    }

    /**
     * switch language
     * @param string $langCode
     */
    public static function switchLang($langCode)
    {
        $languageList = array_keys(self::allLang());
        if (auth()->check()) {
            $langIndex = array_search($langCode, $languageList);
            $langIndex = $langIndex === false ? 1 : $langIndex + 1;
            $user = auth()->user();
            $user->language = $langIndex;
            $user->save();
        }
        Session::set('locale', $langCode);
    }

    /**
     * Change order of function allLang
     * 
     * @param string $code lang code order first
     * @return array
     */
    public static function changeOrder($code)
    {
        $allLang = static::allLang();
        $new = [];
        $new[$code] = $allLang[$code];
        foreach ($allLang as $langKey => $langText) {
            if ($langKey !== $code) {
                $new[$langKey] = $allLang[$langKey];
            }
        }
        return $new;
    }
}
