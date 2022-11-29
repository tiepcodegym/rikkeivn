<?php

namespace Rikkei\Me\Model;

use Rikkei\Project\Model\MeAttribute;
use Rikkei\Core\View\CacheHelper;

class Attribute extends MeAttribute
{
    private static $instance = null;

    /**
     * get instance of this class
     * @return object
     */
    public static function getInstance()
    {
        if (self::$instance !== null) {
            return self::$instance;
        }
        self::$instance = new self();
        return self::$instance;
    }

    /**
     * get attribute by group
     * @param mix integer|array $groups
     * @return collection
     */
    public function getAttrsByGroup($groups)
    {
        return parent::getByGroup($groups);
    }

    /*
     * get attribute ID by type
     */
    public function getAttrIdByType($type)
    {
        if ($attrId = CacheHelper::get('attr_id_by_type_' . $type)) {
            return $attrId;
        }
        $attr = self::where('type', $type)->first();
        if (!$attr) {
            return null;
        }
        CacheHelper::put('attr_id_by_type_' . $type, $attr->id);
        return $attr->id;
    }
}
