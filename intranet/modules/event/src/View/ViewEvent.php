<?php

namespace Rikkei\Event\View;

use Storage;

class ViewEvent
{
    const KEY_CACH_SALARY = 'rikkei_employee_salary_';
    const KEY_CACH_TAX = 'rikkei_employee_tax_';
    const KEY_CACHE_MAILOFF = 'rikkei_it_mail_off_';
    const DELAY_SEND_MAIL = 3; //second
    const ACCESS_FOLDER = 0777;
    const FILE_TYPE_SALARY = 1;
    const FILE_TYPE_TAX = 2;

    /**
     * get heading each column of file fines
     * fron code to index
     */
    public static function getHeadingIndexTetBonus()
    {
        return [
            'no' => 0,
            'full_name' => 1,
            'id' => 2,
            'email' => 3,
            'month_works' => 4,
            'bonus_13' => 5,
            'bonus_tet' => 6,
            'get_total' => 7,
            'tax_bonus_tet' => 8,
            'get_real' => 9,
        ];
    }
    
    /**
     * get heading each column of file fines
     * fron code to index
     */
    public static function getHeadingIndexSabbatical()
    {
        return [
            'id' => 0,
            'full_name' => 1,
            'email' => 2,
            'sabbatical_last_year' => 3,
            'sabbatical_current_year' => 4,
            'sabbatical_seniority' => 5,
            'sabbatical_ot_last_month' => 6,
            'sabbatical_day_total' => 7,
            'sabbatical_day_use' => 8,
            'sabbatical_day_remain' => 9,
        ];
    }
    
    /**
     * get heading each column of file total timekeeping
     * fron code to index
     */
    public static function getHeadingIndexTotalTimekeeping()
    {
        return [
            'id' => 0,
            'email' => 1,
            'full_name' => 2
        ];
    }
    
    /**
     * get heading each column of file total timekeeping
     * fron code to index
     */
    public static function getHeadingIndexTax()
    {
        return [
            'id' => 1,
            'email' => 2,
            'full_name' => 0
        ];
    }
    
    /**
     * get heading each column of file fines
     * fron code to index
     */
    public static function getHeadingIndexFines()
    {
        return [
            'ho_ten' => 0,
            'id' => 1,
            'email' => 2,
            'phut_di_muon' => 3,
            'tien_di_muon' => 4,
            'lan_quen_cham_cong' => 5,
            'tien_quen_cham_cong' => 6,
            'lan_dong_phuc' => 7,
            'tien_dong_phuc' => 8,
            'lan_quen_tat_may' => 9,
            'tien_quen_tat_may' => 10,
            'tong' => 11
        ];
    }

    /**
     * get salary row index
     * @return type
     */
    public static function getSalaryRowIndex()
    {
        return [
            'employee_code' => 0,
            'fullname' => 1,
            'email' => 2,
            'luong_bhxh' => 3,
            'luong_chinh_thuc' => 4,
            'luong_thu_viec' => 5,
            'cong_chinh_thuc' => 6,
            'cong_thu_viec' => 7,
            'ot' => 8
        ];
    }

    /**
     * get salary row index
     * @return type
     */
    public static function getTaxRowIndex()
    {
        return [
            'employee_code' => 0,
            'fullname' => 1,
            'email' => 2
        ];
    }

    /**
     * format money number
     */
    public static function formatMoney($number, $isFormatNumber = true)
    {
        if (!is_numeric($number) || !$isFormatNumber) {
            return $number;
        }
        return number_format($number, 0, ',', '.');
    }

    /**
     * get value from array
     * @param type $collectCols
     * @param type $index
     * @return type
     */
    public static function getValueSalary($collectCols, $index)
    {
        foreach (array_reverse($collectCols) as $columns) {
            if (isset($columns[$index])) {
                return $columns[$index]['title'];
            }
        }
        return null;
    }

    /**
     * get sever post max size upload
     * @return type
     */
    public static function getPostMaxSize()
    {
        return preg_replace('/[^0-9\.]/', '', ini_get('post_max_size')) * 1024;
    }

    /*
     * get email content, email subject key in CoreConfigData
     */
    public static function getKeysEmailBranch($teamCode, $key)
    {
        $keyContentEmail = 'hr.email_content.' . $key;
        $keySubjectEmail = 'hr.email_subject.' . $key;
        if ($teamCode != \Rikkei\Team\Model\Team::CODE_PREFIX_HN) {
            $keyContentEmail .= '.' . $teamCode;
            $keySubjectEmail .= '.' . $teamCode;
        }
        return [
            'content' => $keyContentEmail,
            'subject' => $keySubjectEmail
        ];
    }

    /*
     * create directory
     */
    public static function createDir($path)
    {
        if (!Storage::exists($path)) {
            Storage::makeDirectory($path, self::ACCESS_FOLDER);
        }
        @chmod(storage_path('app/' . $path), self::ACCESS_FOLDER);
    }

    /*
     * get email content, email subject key in CoreConfigData
     */
    public static function getKeysEmailForgotTurnOff($teamCode)
    {
        $keyContentEmail = 'hr.email_content.turnoff';
        $keySubjectEmail = 'hr.email_subject.turnoff';
        if ($teamCode != \Rikkei\Team\Model\Team::CODE_PREFIX_HN) {
            $keyContentEmail .= '.' . $teamCode;
            $keySubjectEmail .= '.' . $teamCode;
        }
        return [
            'content' => $keyContentEmail,
            'subject' => $keySubjectEmail
        ];
    }

    public static function getHeadingIndexForgotTurnOff()
    {
        return [
            'name' => 'name',
            'account' => 'account',
            'ip_address' => 'ip_address',
            'computer_name' => 'computername',
            'date' => 'date',
            'month' => 'month',
        ];
    }

}
