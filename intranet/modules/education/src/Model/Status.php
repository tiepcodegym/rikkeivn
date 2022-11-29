<?php

namespace Rikkei\Education\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;

class Status extends CoreModel
{
    const STATUS_NEW = 1;
    const STATUS_SUBMITTED = 2;
    const STATUS_OPEN = 3;
    const STATUS_PENDING = 4;
    const STATUS_CLOSED = 5;

    const RESULT_COMPLETE = 1;
    //role
    const ROLE_HOCVIEN = 1;
    const ROLE_GIANGVIEN = 2;

    static $RESULT = [
        self::RESULT_COMPLETE  => 'education::message.complete'
    ];

    static $STATUS = [
        self::STATUS_NEW => 'education::message.create_new',
        self::STATUS_SUBMITTED => 'education::message.register',
        self::STATUS_OPEN => 'education::message.open',
        self::STATUS_PENDING => 'education::message.pending',
        self::STATUS_CLOSED => 'education::message.closed'
    ];

    // role

    static $ROLE = [
        self::ROLE_HOCVIEN => 'education::message.student',
        self::ROLE_GIANGVIEN => 'education::message.lecturers',
    ];
    /**
     * get priority label of task
     *
     * @return array
     */
    public static function statusLabel()
    {
        return [
            self::STATUS_NEW => 'education::message.create_new',
            self::STATUS_SUBMITTED => 'education::message.register',
            self::STATUS_OPEN => 'education::message.open',
            self::STATUS_PENDING => 'education::message.pending',
            self::STATUS_CLOSED => 'education::message.closed',
        ];
    }

    public static function roleLabel()
    {
        return [
            self::ROLE_HOCVIEN => 'education::message.student',
            self::ROLE_GIANGVIEN => 'education::message.lecturers',
        ];
    }
}
