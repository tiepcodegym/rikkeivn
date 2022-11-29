<?php

namespace Rikkei\Music\View;
use Rikkei\Music\Model\MusicOffice;

class ViewMusic
{
    const IS_PLAYED = 1;
    const NOT_PLAYED = 0;
    const HAS_VOTE = 1;

    /**
    * short message
    */ 
    public static function shortMess($mess, $max, $break = false)
    {
        // if ($break) {
        //     $arrMess = explode(PHP_EOL, $mess);
        //     if (count($arrMess) < 2) {
        //         $arrMess = explode("<br />", $mess);
        //     }
        //     if (count($arrMess) > 1) {
        //         $mess = $arrMess[0];
        //     }
        // }
        // $countWords = str_word_count($mess);
        // if ($countWords < $max){
        //     return $mess;
        // }
        $result = "";
        $small = explode(' ', $mess);
        for($i = 0; $i < count($small); $i++){
            if(isset($small[$i])){
                $result = $result." ".$small[$i];
            }
        }
        return $result;
    }

    public static function shortTime($stringTime) 
    {
        $times = explode(":", $stringTime);
        return $times[0].":".$times[1];
    }
    
    /**
     * Get Menu Music
     * 
     * @return array
     */
    public static function getMenuMusic(){
        return MusicOffice::getOffices();
    }
    
    /**
     * Get Office Name
     * @param type $officeId
     * @return string
     */
    public static function getOffice($officeId){
        return MusicOffice::getOffice($officeId);
    }

    /**
     * format number
     * 
     * @param type $total
     * @return string
     */
    public static function compactTotal($total, &$totalReal = 0, &$greater = false)
    {
        $totalReal = $total;
    	if ($total < 1000) {
            return $total;
        }
        $greater = true;
        if($total < 1000000){
            return round($total/1000) . 'K';
        }
        return round($total/1000000) . 'M';
    }

    public static function getPage($currentPage, $firstPage, $lastPage, $totalPage) 
    {
        $isFirst = false;
        $isLast = false;
        if($currentPage == $firstPage) {
            $isFirst = true;
        }elseif ($currentPage == $lastPage) {
            $isLast = true;
        }
        if($lastPage < $totalPage) {
            return ['start' => $firstPage,
                    'end' => $lastPage,
                    'isFirst' => $isFirst,
                    'isLast' => $isLast];
        }
        if($currentPage < $firstPage + floor($totalPage/2)) {
            $startPage = $firstPage;
            $endPage = $startPage + ($totalPage-1);
        }else {
            $startPage = $currentPage - floor($totalPage/2);
            $endPage = $startPage + ($totalPage-1);
            if($endPage>$lastPage) {
                $endPage = $lastPage;
                $startPage = $lastPage - ($totalPage - 1);
            }
        }
        return ['start' => $startPage,
                'end' => $endPage,
                'isFirst' => $isFirst,
                'isLast' => $isLast];
    }
}
