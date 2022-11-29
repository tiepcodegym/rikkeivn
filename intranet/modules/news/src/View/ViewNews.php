<?php

namespace Rikkei\News\View;

use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Rikkei\News\Model\PostComment;

class ViewNews
{
    const FOLDER_UPLOAD = 'news';
    const FILE_UPLOAD = 'posts.html';
    const FOLDER_APP = 'app';

    const ACCESS_FOLDER = 0777;
    const ACCESS_FILE = 'public';

    /**
     * get url of category
     *
     * @param string $slug
     * @return string
     */
    public static function getCategoryUrl($slug)
    {
        $reg = '/^\{\{\sbaseUrl\s\}\}/';
        if (preg_match($reg, $slug)) {
            return preg_replace($reg, URL::to('/'), $slug);
        }
        return URL::route('news::post.index.cat', ['slug' => $slug]);
    }

    /**
     * general slug
     *
     * @param string $slug
     * @return sring
     */
    public static function generalSlug($slug)
    {
        $reg = '/^\{\{\sbaseUrl\s\}\}/';
        if (preg_match($reg, $slug)) {
            return $slug;
        }
        return Str::slug($slug);
    }

    /**
     * get file upload
     *
     * @param string $content
     */
    public static function createFileTemplateEmail($content)
    {
        if (!Storage::exists(self::FOLDER_UPLOAD)) {
            Storage::makeDirectory(self::FOLDER_UPLOAD, self::ACCESS_FOLDER);
        }
        @chmod(storage_path(self::FOLDER_APP . '/' . self::FOLDER_UPLOAD),
            self::ACCESS_FOLDER);
        Storage::put(self::FOLDER_UPLOAD . '/' . self::FILE_UPLOAD, $content, self::ACCESS_FILE);
        @chmod(storage_path(self::FOLDER_APP . '/' . self::FILE_UPLOAD),
            self::ACCESS_FOLDER);
    }

    /**
     * check template file
     *
     * @return boolean
     */
    public static function existsTemplate($string = false)
    {
        if (Storage::exists(self::FOLDER_UPLOAD . '/' . self::FILE_UPLOAD)) {
            if ($string) {
                return Storage::get(self::FOLDER_UPLOAD . '/' . self::FILE_UPLOAD);
            }
            return true;
        }
        return false;
    }

    /**
     * cut text html
     *
     * @param string $text
     * @param int $length
     * @param boolean $gt
     * @return string
     */
    public static function cutTextHtml($text, $length = 200, &$gt = true)
    {
        $text = trim(strip_tags($text));
        if (Str::length($text) > $length) {
            $gt = true;
            $space = mb_strpos($text, ' ', $length);
            return Str::substr($text, 0, $space);
        }
        $gt = false;
        return $text;
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
        if ($total < 1000000) {
            return round($total / 1000, 1) . 'K';
        }
        return round($total / 1000000, 1) . 'M';
    }


    /**
     * short description
     */
    public static function shortDesc($desc, $max)
    {
        $desc = strip_tags($desc);
        $countWords = str_word_count($desc);
        $result = "";
        $small = preg_split("/[\s,]+/ ", $desc);
        if ($countWords <= $max) {
            return $desc;
        }

        if ($max > count($small)) {
            $max = count($small);
        }
        for ($i = 0; $i < $max; $i++) {
            if (isset($small[$i])) {
                $result = $result . " " . $small[$i];
            }
        }
        return $result . "...";
    }

    /**
     * short author
     */
    public static function shortAuthor($author, $max)
    {

        if (strlen($author) <= $max) {
            return $author;
        }

        $result = "";

        $small = explode(" ", $author);

        for ($i = 0; $i < count($small); $i++) {
            if (isset($small[$i])) {
                $result = $result . " " . $small[$i];
                if (strlen($result) > $max) {
                    $result = substr($result, 0, -(strlen($small[$i])));
                    return $result . "...";
                }
            }
        }
    }

    /**
     * @param $dateTime
     * @return false|string
     */
    public static function formatDateTime($dateTime)
    {
        return date_format($dateTime, 'd \T\h\á\n\g m Y - H:i');
    }

    /**
     * @param integer $parentId
     * @param integer $perPage
     * @param integer $page
     * @return integer mixed
     */
    public static function getReplyComment($parentId, $perPage, $page, $userInfo)
    {
        return PostComment::getAllReplyComment($parentId, $perPage, $page, $userInfo);
    }

    /**
     * @param integer $parentId
     * @return mixed
     */
    public static function getTotalReplyCommentByParentId($parentId)
    {
        return PostComment::where('parent_id', $parentId)->count();
    }

    /**
     * Option trim comment content to show if content too long
     * @return array
     */
    public static function getOptionTrimWord()
    {
        return ['num_ch' => PostComment::MAX_WORD_DISPLAY, 'num_line' => PostComment::MAX_LINE_DISPLAY];
    }

    /**
     * Check number line of comment > PostComment::MAX_LINE_DISPLAY
     * Check length string comment > PostComment::MAX_WORD_DISPLAY
     * @param string $comment
     * @return boolean
     */
    public static function hasViewMore($comment)
    {
        $numLine = PostComment::MAX_LINE_DISPLAY;
        $arrayLines = preg_split("/[\r\n]+/", $comment, $numLine + 1, PREG_SPLIT_NO_EMPTY);
        return count($arrayLines) > $numLine || mb_strlen($comment) > PostComment::MAX_WORD_DISPLAY;
    }

    /**
     * Get diff days between 2 date
     *
     * @param string|date $date1
     * @param string|date $date2
     * @return int
     */
    public static function getDiffDay($date1, $date2 = null)
    {
        if (!$date2) {
            $date2 = date('Y-m-d');
        }
        $date2 = Carbon::parse($date2);
        $date1 = Carbon::parse(date('Y-m-d', strtotime($date1)));
        return $date1->diffInDays($date2);
    }

    public static function formatDateTimeComment($dateTime)
    {
        return date_format($dateTime, 'd/m/Y');
    }

}
