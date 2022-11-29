<?php

namespace Rikkei\Sales\View;

use Rikkei\Team\Model\Employee;
use Rikkei\Sales\Model\Css;

class View {
    /**
     * Get username from email
     * 
     * @param string $email
     * @return string
     */
    public static function getAccName($email) {
        return preg_replace('/@.*?$/', '', $email);
    }

    /**
     * Get account without team Sales for company's account manager
     *
     * @return Employee collection
     */
    public static function companyManagerExtract()
    {
        $extracts = [
            'huybq@rikkeisoft.com',
        ];

        return Employee::getEmpByEmails($extracts, ['id', 'email']);
    }

    public static function checkLang($lang, $project_type_id, $lang_id, $created_at)
    {
        if ($created_at < Css::CSS_TIME) {
            if ($project_type_id != Css::TYPE_ONSITE && $lang_id == Css::VIE_LANG) {
                $lang = 'en';
            }
        }
        return $lang;
    }

    public static function checkLangOvvQuestion($project_type_id, $lang_id, $created_at)
    {
        return ($project_type_id == Css::TYPE_ONSITE && $created_at < Css::CSS_TIME) ? $lang_id : null;
    }

    public static function renderQsExplain($expain = null)
    {
        if (!$expain) {
            return '';
        }
        $expain = json_decode($expain, true);
        $html = '';
        if ($expain) {
        $html .='<div class="qs-explain">
                    <li>
                        <div class="explain-star">
                            <span class="fa fa-star checked"></span>
                            <span class="fa fa-star checked"></span>
                            <span class="fa fa-star checked"></span>
                            <span class="fa fa-star checked"></span>
                            <span class="fa fa-star checked"></span>
                        </div>
                        <div class="explain-text">';
                            $html.= isset($expain[5]) ? $expain[5] : '';
                $html.='</div>
                    </li>
                    <li>
                        <div class="explain-star">
                            <span class="fa fa-star checked"></span>
                            <span class="fa fa-star checked"></span>
                            <span class="fa fa-star checked"></span>
                            <span class="fa fa-star checked"></span>
                        </div>
                        <div class="explain-text">';
                            $html.= isset($expain[4]) ? $expain[4] : '';
                $html.='</div>
                    </li>
                    <li>
                        <div class="explain-star">
                            <span class="fa fa-star checked"></span>
                            <span class="fa fa-star checked"></span>
                            <span class="fa fa-star checked"></span>
                        </div>
                        <div class="explain-text">';
                            $html.= isset($expain[3]) ? $expain[3] : '';
                $html.='</div>
                    </li>
                    <li>
                        <div class="explain-star">
                            <span class="fa fa-star checked"></span>
                            <span class="fa fa-star checked"></span>
                        </div>
                        <div class="explain-text">';
                        $html.= isset($expain[2]) ? $expain[2] : '';
                $html.= '</div>
                    </li>
                    <li>
                        <div class="explain-star">
                            <span class="fa fa-star checked"></span>
                        </div>
                        <div class="explain-text">';
                            $html.= isset($expain[1]) ? $expain[1] : '';
                $html.= '</div>
                    </li>';
                    if (isset($expain[0])) {
                        $html.= '<li>
                                    <div class="explain-star">
                                        &#60; blank &#62;
                                    </div>
                                    <div class="explain-text">';
                                        $html.= isset($expain[0]) ? $expain[0] : '';
                            $html.= '</div>
                                </li>';
                    }
        $html.= '</div>';
        }
        return $html;
    }
}
