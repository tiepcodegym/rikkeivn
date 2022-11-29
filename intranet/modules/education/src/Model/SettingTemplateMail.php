<?php

namespace Rikkei\Education\Model;

use Rikkei\Core\Model\CoreModel;

class SettingTemplateMail extends CoreModel
{
    protected $table = 'setting_template_mails';

    const TEMPLATE_INVITE = 'invite';
    const TEMPLATE_REMINDER = 'reminder';
    const TEMPLATE_VOCATIONAL  = 'vocational';
    const TEMPLATE_THANK  = 'thank';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'template'];


    /**
     * get full label type template mail
     */
    public static function labelTypeTemplateFull() {
        return [
            self::TEMPLATE_INVITE => trans('education::view.Template email of course details'),
            self::TEMPLATE_REMINDER => trans('education::view.Template Email reminder form to join the course'),
            self::TEMPLATE_VOCATIONAL => trans('education::view.Template Invitation letter to join the job transfer'),
            self::TEMPLATE_THANK => trans('education::view.Thanks for finishing the course'),
        ];
    }
}
