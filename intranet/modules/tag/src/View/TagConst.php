<?php

namespace Rikkei\Tag\View;

use Rikkei\Project\Model\Project;

class TagConst
{
    const SET_TAG_PROJECT = 1;
    const SET_TAG_EMPLOYEE = 2;
    
    const FIELD_STATUS_ENABLE = 1;
    const FIELD_STATUS_DISABLE = 2;
    
    const FIELD_TYPE_INPUT = 1;
    const FIELD_TYPE_RADIO = 2;
    const FIELD_TYPE_TEXTAREA = 3;
    const FIELD_TYPE_EDITOR = 4;
    const FIELD_TYPE_SELECT = 5;
    const FIELD_TYPE_MULTI_SELECT = 6;
    const FIELD_TYPE_TAG = 10;
    const FIELD_TYPE_INFO = 20;

    const TAG_STATUS_APPROVE = 1; //use both tag and project
    const TAG_STATUS_REVIEW = 2; //use both tag and project
    
    const TAG_STATUS_DRAFT = 3; // use only tag
    
    const TAG_STATUS_NOT_ASSIGN = 3; //use only project
    const TAG_STATUS_ASSIGNED = 4; //use only project
    
    const ACTION_APPROVE = 1;
    const ACTION_SUBMIT = 2;
    const ACTION_ASSIGN = 3;
    const ACTION_UPDATE_TAG = 4; //add or delete
    
    const NUM_SHOW_TAGS = 25;
    const COLOR_DEFAULT = '#384d68';
    
    const MAX_TAG_OF_FIELD = 10;
    
    const KEY_CONFIT_LDB_VERSION = 'tag.ldb.version';
    const KEY_TAG_VER = 'storage.tag.version';
    
    /*
     * route allow permission
     */
    const RA_VIEW_SEARCH = 'tag::view.proj.search';
    const RA_PROJ_OLD_EDIT = 'tag::manage.project.old';
    const ROUTE_VIEW_PROJ_TAG = 'tag::view.proj.tagging';
    const ROUTE_VIEW_PROJ_SEARCH = 'tag::view.proj.search';
    const ROUTE_VIEW_PROJ_DETAIL = 'tag::view.proj.detail';
    const ROUTE_SUBMIT_PROJ_TAG = 'tag::post.proj.submit.tag';
    const ROUTE_APPROVE_PROJ_TAG = 'tag::post.proj.approve.tag';
    
    /**
     * get field status list
     * 
     * @return array
     */
    public static function fieldStatus()
    {
        return [
            self::FIELD_STATUS_ENABLE => 'Enable',
            self::FIELD_STATUS_DISABLE => 'Disable',
        ];
    }
    
    /**
     * get field types
     * 
     * @return array
     */
    public static function fieldTypes()
    {
        return [
            self::FIELD_TYPE_TAG => 'Tag',
        ];
    }
    
    /**
     * get status tag
     * 
     * @return array
     */
    public static function tagStatus()
    {
        return [
            self::TAG_STATUS_APPROVE => 'Approve',
            self::TAG_STATUS_REVIEW => 'Review',
        ];
    }
    
    /**
     * state of project old
     * 
     * @return array
     */
    public static function projectState()
    {
        return [
            Project::STATUS_OLD => 'Old',
        ];
    }
    
    /**
     * state of project old
     * 
     * @return array
     */
    public static function projectTypeResource()
    {
        return [
            Project::MD_TYPE => 'MD',
            Project::MM_TYPE => 'MM'
        ];
    }
    
    /**
     * get proj tag list statuses
     * @return array
     */
    public static function projTagStatus () {
        return [
            self::TAG_STATUS_APPROVE => 'Approved',
            self::TAG_STATUS_REVIEW => 'Submitted',
            self::TAG_STATUS_ASSIGNED => 'Assigned',
            self::TAG_STATUS_NOT_ASSIGN => 'Not assign'
        ];
    }
    
    /**
     * get list actions
     * @return array
     */
    public static function tagActionClasses () {
        return [
            self::TAG_STATUS_APPROVE => '',
            self::TAG_STATUS_REVIEW => 'tag-review'
        ];
    }
    
}
