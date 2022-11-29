<?php

namespace Rikkei\Vote\View;

use Rikkei\Team\View\Permission;

class VoteConst {
    
    const STT_ENABLE = 1;
    const STT_DISABLE = 2;
    
    const CONFIRM_NO = 0;
    const CONFIRM_YES = 1;
    
    const TEXT_NO = 'no';
    const TEXT_YES = 'yes';
    
    /**
     * get list vote status
     * @return type
     */
    public static function getVoteStatuses () {
        return [
            self::STT_ENABLE => trans('vote::view.enable'),
            self::STT_DISABLE => trans('vote::view.disable')
        ];
    }

    /**
     * get confirm label
     * @param type $confirm
     * @return type
     */
    public static function getConfirmLabel ($confirm) {
        if ($confirm === self::CONFIRM_NO) {
            return trans('vote::view.confirm_no');
        }
        if ($confirm === self::CONFIRM_YES) {
            return trans('vote::view.confirm_yes');
        }
        return trans('vote::view.confirm_not_yet');
    }
    
    /**
     * check permission edit
     * @param type $vote
     * @return boolean
     */
    public static function hasPermissEdit ($vote = null, $route = null) {
        $scope = Permission::getInstance();
        
        if ($scope->isScopeCompany(null, $route) || $scope->isScopeTeam(null, $route)) {
            return true;
        }
        if ($scope->isScopeSelf(null, $route)) {
            if (!$vote) {
                return true;
            }
            if (!$vote->created_by) {
                return true;
            }
            return ($scope->getEmployee()->id == $vote->created_by);
        }
        return false;
    }
    
    /**
     * check permission create
     * @param type $route
     * @return type
     */
    public static function hasPermissCreate ($route = 'vote::manage.vote.create') {
        return Permission::getInstance()->isAllow($route);
    }
    
    /**
     * trim word
     * @param type $content
     * @param type $length
     * @param type $more
     */
    public static function trimWords ($content, $ch_len = 100, $word_len = 15, $more = '...') {
        if (strlen($content) > $ch_len) {
            $content = substr($content, 0, $ch_len) . $more;
        } else {
            $words = explode(' ', $content);
            if (count($words) > $word_len) {
                $words = array_slice($words, 0, $word_len);
                $content = implode(' ', $words) . $more;
            }
        }
        return $content;
    }
    
}

