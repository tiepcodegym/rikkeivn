<?php

namespace Rikkei\Core\View;

class OptionCore
{
    /**
     * option yes no 
     * 
     * @param type $null
     */
    public static function yesNo($null = true, $shortLabel = false)
    {
        $option = [];
        if ($null) {
            $option[''] = '&nbsp';
        }
        if ($shortLabel) {
            $option[1] = 'Y';
            $option[2] = 'N';
        } else {
            $option[1] = 'Yes';
            $option[2] = 'No';
        }
        return $option;
    }

    /**
     * set memory for php core
     */
    public static function setMemoryMax(array $option = [])
    {
        $option = array_merge([
            'max_execution_time' => 300,
            'max_input_time' => 600,
            'memory_limit' => '512M',
        ], $option);
        ini_set('max_execution_time',$option['max_execution_time']);
        ini_set('max_input_time',$option['max_input_time']);
        ini_set('memory_limit',$option['memory_limit']);
    }
}
