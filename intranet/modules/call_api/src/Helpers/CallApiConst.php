<?php

namespace Rikkei\CallApi\Helpers;

class CallApiConst
{
    const ACL_SONAR = 'project::project.add_critical_dependencies';
    const SONAR_PERMISSIONS_PROJ = [
        'admin' => 'admin',
        'issueadmin' => 'issueadmin',
        'user' => 'user',
        'scan' => 'scan',
        'codeviewer' => 'codeviewer',
    ];

    /**
     * get permission of dev in sonar
     *
     * @return array
     */
    public static function sonarPermisDev()
    {
        return [self::SONAR_PERMISSIONS_PROJ['scan'], self::SONAR_PERMISSIONS_PROJ['codeviewer']];
    }
}
