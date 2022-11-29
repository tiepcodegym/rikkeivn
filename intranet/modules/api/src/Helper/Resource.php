<?php

namespace Rikkei\Api\Helper;

use Rikkei\Api\Helper\Base as BaseHelper;
use Rikkei\Resource\Model\Languages;
use Rikkei\Resource\Model\Programs;
use Rikkei\Resource\Model\WorkPlace;

/**
 * Description of Contact
 *
 * @author lamnv
 */
class Resource extends BaseHelper
{
    /**
     * Get data
     *
     * @return array
     * @throws \Exception
     */
    public function getData()
    {
        $places = WorkPlace::getInstance()->getList();
        $programingLanguage = Programs::getInstance()->getList();
        $languages = Languages::getInstance()->getList();
        return [
            'places' => $places,
            'programingLanguages' => $programingLanguage,
            'languages' => $languages,
        ];
    }
}
