<?php

namespace Rikkei\Document\View;

use Storage;

class DocConst
{
    //upload dir
    const UPLOAD_DIR = 'document';
    const EXCLUDE_EXT = ['exe'];
    //type status
    const STT_ENABLE = 1;
    const STT_DISABLE = 2;
    //document status
    const STT_NEW = 1;
    const STT_SUBMITED = 2;
    const STT_REVIEWED = 3;
    const STT_APPROVED = 4;
    const STT_FEEDBACK = 5;
    const STT_PUBLISH = 10;
    //type assigne
    const TYPE_ASSIGNE_REVIEW = 3;
    const TYPE_ASSIGNE_APPROVE = 4;
    const TYPE_ASSIGNE_PUBLISH = 10;
    const TYPE_ASSIGNE_EDITOR = 11;
    //custom perpage
    const HISTORY_PER_PAGE = 20;
    const COMMENT_PER_PAGE = 20;
    const DOC_PER_PAGE = 20;
    //permission route
    const ROUTE_MANAGE_DOC = 'doc::permis.doc.manage';
    const ROUTE_MANAGE_REQUEST = 'doc::permis.request.manage';
    const ROUTE_APPROVE_REQUEST = 'doc::permis.request.approve';
    const ROUTE_PUBLISH_DOC = 'doc::permiss.doc.publish';
    const ROUTE_REVIEW_DOC = 'doc::permiss.doc.review';
    //type
    const TYPE_DOC = 1;
    const TYPE_ATTACH = 2;
    //comment type
    const COMMENT_TYPE_FEEDBACK = 2;
    // publish all
    const PUBLISH_ALL = 1;

    /**
     * list type status
     * @return array
     */
    public static function listTypeStatuses()
    {
        return [
            self::STT_ENABLE => 'Enable',
            self::STT_DISABLE => 'Disable'
        ];
    }

    /**
     * list document status
     * @return array
     */
    public static function listDocStatuses()
    {
        return [
            self::STT_NEW => 'New',
            self::STT_SUBMITED => 'Submited',
            self::STT_REVIEWED => 'Reviewed',
            self::STT_APPROVED => 'Approved',
            self::STT_FEEDBACK => 'Feedbacked',
            self::STT_PUBLISH => 'Published'
        ];
    }

    /**
     * list request status
     * @return array
     */
    public static function listRequestStatuses()
    {
        return [
            self::STT_NEW => 'New',
            self::STT_SUBMITED => 'Submited',
            self::STT_APPROVED => 'Approved',
            self::STT_FEEDBACK => 'Feedback'
        ];
    }

    public static function getLabelStatus($status, $labels = [])
    {
        if (!$labels) {
            $labels = self::listDocStatuses();
        }
        if (isset($labels[$status])) {
            return $labels[$status];
        }
        return null;
    }

    public static function renderStatusHtml($status, $statuses, $class = 'callout')
    {
        $html = '<div class="'. $class .' text-center white-space-nowrap ' . $class;
        switch ($status) {
            case DocConst::STT_NEW:
                $html .=  '-default">' . $statuses[$status];
                break;
            case DocConst::STT_SUBMITED:
                $html .= '-warning">' . $statuses[$status];
                break;
            case DocConst::STT_REVIEWED:
                $html .= '-info">' . $statuses[$status];
                break;
            case DocConst::STT_APPROVED:
                $html .= '-success">' . $statuses[$status];
                break;
            case DocConst::STT_PUBLISH:
                $html .= '-publish">' . $statuses[$status];
                break;
            case DocConst::STT_FEEDBACK:
                $html .= '-danger">' . $statuses[$status];
                break;
            default:
                return null;
        }
        return $html .= '</div>';
    }

    /**
     * to nested options
     * @param type $collection
     * @param type $selected
     * @param type $parent
     * @param type $depth
     * @return string
     */
    public static function toNestedOptions($collection, $selected = null, $parent = null, $depth = 0)
    {
        if ($collection->isEmpty()) {
            return '';
        }
        $html = '';
        $indent = str_repeat('-- ', $depth);
        if (!$selected) {
            $selected = [];
        }
        if (!is_array($selected)) {
            $selected = [$selected];
        }
        foreach ($collection as $item) {
            if ($item->parent_id == $parent) {
                $html .= '<option value="'. $item->id .'" '. (in_array($item->id, $selected) ? 'selected' : '') .'>'
                        . $indent . e($item->name) . '</option>';
                $html .= self::toNestedOptions($collection, $selected, $item->id, $depth + 1);
            }
        }
        return $html;
    }

    /**
     * list nested checkbox type
     * @param collection $collection
     * @param array $checked
     * @param int $parent
     * @param int $depth
     * @return string
     */
    public static function toNestedCheckbox(
        $collection,
        $checked = [],
        $name = 'type_ids[]',
        $disabled = false,
        $parent = null,
        $depth = 0
    )
    {
        if ($collection->isEmpty()) {
            return '';
        }
        $html = '';
        $indent = str_repeat("&nbsp;", $depth * 8);
        foreach ($collection as $item) {
            if ($item->parent_id == $parent) {
                $html .= '<li data-id="'. $item->id .'" data-depth="'. $depth .'">'
                        . $indent . '<label>'
                        . '<input type="checkbox" name="'. $name .'" value="'. $item->id .'" '
                        . (in_array($item->id, $checked) ? 'checked' : '')
                        . ' '. ($disabled ? "disabled" : '') .'> '
                        . e($item->name)
                        . '</label>'
                        . '</li>';
                $html .= self::toNestedCheckbox($collection, $checked, $name, $disabled, $item->id, $depth + 1);
            }
        }
        return $html;
    }

    /**
     * render list view
     * @param type $collection
     * @param type $parent
     * @param type $depth
     * @return string
     */
    public static function toNestedList($collection, $parent = null, $depth = 0, $activeId = null)
    {
        if ($collection->isEmpty()) {
            return '';
        }
        $html = '';
        foreach ($collection as $item) {
            if ($item->parent_id == $parent) {
                $html .= '<li class="depth-'. $depth . ($activeId == $item->id ? ' active' : '') .'">'
                        . '<div class="inner-list">';
                if ($depth == 0) {
                    $html .= '<i class="fa fa-folder"></i> ';
                } else {
                    $html .= '<i class="fa fa-angle-right"></i>';
                }
                $childHtml = self::toNestedList($collection, $item->id, $depth + 1, $activeId);
                $html .= '<a href="'. $item->getDocViewLink() .'">' . e($item->name) . '</a>';
                if ($childHtml) {
                    $html .= '<b class="fa fa-plus toggle-icon"></b></div>';
                    $html .= '<ul class="list-child" style="display: none;">' . $childHtml . '</ul>';
                } else {
                    $html .= '</div>';
                }
                $html .= '</li>';
            }
        }
        return $html;
    }

    /**
     * get min file max size
     * @return int
     */
    public static function fileMaxSize()
    {
        $size = 50; //mb
        $maxSize = trim(ini_get('post_max_size'));
        $maxSize = (int) substr($maxSize, 0, strlen($maxSize) - 1);
        return min([$size, $maxSize]) * 1024;
    }

    /**
     * make upload directory
     * @param string $dir
     */
    public static function makeUploadDir($dir = null)
    {
        if (!$dir) {
            $dir = self::UPLOAD_DIR;
        }
        if (!Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir, 0777);
        }
    }

    /**
     * render list type name
     * @param string separator by "|" $strTypeIds
     * @param array $listTypes
     * @return string
     */
    public static function getListTypeName($strTypeIds, $listTypes, $isList = true)
    {
        $arrIds = explode('|', $strTypeIds);
        if (!$arrIds) {
            return null;
        }
        $liOpen = '';
        $liClose = ', ';
        if ($isList) {
            $liOpen = '<li>';
            $liClose = '</li>';
        }
        $render = '';
        foreach ($arrIds as $id) {
            if (isset($listTypes[$id])) {
                $render .= $liOpen . e($listTypes[$id]) . $liClose;
            }
        }
        if (!$isList) {
            $render = trim($render, $liClose);
        }
        return $render;
    }

    /**
     * get account by email
     * @param type $email
     * @return type
     */
    public static function getAccount($email)
    {
        return ucfirst(strtolower(preg_replace('/@.*/', '', $email)));
    }

    public static function getListAccount($collect)
    {
        if ($collect->isEmpty()) {
            return null;
        }
        $result = [];
        foreach ($collect as $emp) {
            $result[] = $emp->name . ' ('. self::getAccount($emp->email) . ')';
        }
        return implode(', ', $result);
    }

    public static function getFileSrc($url, $checkExists = true, $type = 'file')
    {
        if ($type == 'link') {
            return $url;
        }
        $uploadDir = trim(DocConst::UPLOAD_DIR, '/');
        $srcFile = $uploadDir . '/' . $url;
        if ($checkExists && !Storage::disk('public')->exists($srcFile)){
            return null;
        }
        return '/storage/' . $srcFile;
    }

    public static function compareArray($array1, $array2)
    {
        $diff1 = array_diff($array1, $array2);
        $diff2 = array_diff($array2, $array1);
        if (!$diff1 && !$diff2) {
            return true;
        }
        return false;
    }

    public static function getOldEmployee($id)
    {
        $returnFirst = false;
        if (!is_array($id)) {
            $returnFirst = true;
            $id = [$id];
        }
        $list = \Rikkei\Team\Model\Employee::whereIn('id', $id);
        if ($returnFirst) {
            return $list->first();
        }
        return $list->get();
    }

    public static function getOldRequest($id)
    {
        return \Rikkei\Document\Models\DocRequest::find($id);
    }

    /**
     * get team by old team ids
     * @param type $id
     * @return object|collection
     */
    public static function getOldTeams($id)
    {
        $returnFirst = false;
        if (!is_array($id)) {
            $returnFirst = true;
            $id = [$id];
        }
        $list = \Rikkei\Team\Model\Team::whereIn('id', $id);
        if ($returnFirst) {
            return $list->first();
        }
        return $list->get();
    }

    /**
     * check has permiss company manage document
     * @return boolean
     */
    public static function isPermissCompany()
    {
        return \Rikkei\Team\View\Permission::getInstance()->isScopeCompany(null, self::ROUTE_MANAGE_DOC);
    }
}

