<?php

namespace Rikkei\Core\View;

use Storage;

class CoreFile
{
    protected static $instance = null;
    const ACCESS_PUBLIC = 0777;

    /**
     * get instance of class
     * @return object
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * create folder in storage/app
     * @param string $path folder path
     */
    public function createDir($path)
    {
        if (!Storage::exists($path)) {
            Storage::makeDirectory($path, self::ACCESS_PUBLIC);
        }
        @chmod(storage_path('app/' . $path), self::ACCESS_PUBLIC);
    }

    /**
     * get only file name except extension
     * @param string $clientName
     * @return string
     */
    public function getOnlyFileName($clientName)
    {
        $aryName = explode('.', $clientName);
        if (count($aryName) == 1) {
            return $clientName;
        }
        array_splice($aryName, count($aryName) - 1, 1);
        return implode('.', $aryName);
    }

    /**
     * to slug file name
     * @param string $fileName
     * @return string
     */
    public function toSlugName($fileName)
    {
        $aryName = explode('.', $fileName);
        if (count($aryName) == 1) {
            return str_slug($fileName);
        }
        $len = count($aryName);
        $ext = $aryName[$len - 1];
        unset($aryName[$len - 1]);
        $name = str_slug(implode('.', $aryName));
        return $name . '.' . $ext;
    }

    /**
     * get server config post_max_size
     * @param integer $size default max file size
     * @return integer
     */
    public function getMaxFileSize($size = 8)
    {
        $maxSize = trim(ini_get('post_max_size'));
        $maxSize = (int) substr($maxSize, 0, strlen($maxSize) - 1);
        return min([$size, $maxSize]) * 1024;
    }
}
