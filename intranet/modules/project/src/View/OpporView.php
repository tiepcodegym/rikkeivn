<?php

namespace Rikkei\Project\View;

use Carbon\Carbon;

class OpporView
{
    /**
     * render list account from email string
     * @param string $strEmails
     * @param string $separator
     * @return string
     */
    public static function renderAccount($strEmails, $separator = ', ')
    {
        $arrEmail = explode(',', $strEmails);
        if (!$arrEmail) {
            return null;
        }
        $result = [];
        foreach ($arrEmail as $email) {
            $result[] = ucfirst(preg_replace('/\s|@.*/', '', $email));
        }
        return implode($separator, $result);
    }

    public static function validateStartAt($startAt, $endAt)
    {
        $startAt = Carbon::createFromFormat('Y-m-d', $startAt);
        $endAt = Carbon::createFromFormat('Y-m-d', $endAt);
        return $startAt->lte($endAt);
    }
}

