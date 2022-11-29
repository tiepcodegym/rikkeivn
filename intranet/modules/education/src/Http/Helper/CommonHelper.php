<?php
namespace Rikkei\Education\Http\Helper;

use Rikkei\Api\Helper\Team;
use Rikkei\Team\Model\Permission;
use Rikkei\Team\Model\TeamMember;

class CommonHelper
{
    /**
     *  store this object
     * @var object
     */
    protected static $instance;

    public function __construct() {

    }

    /**
     * Get Team Id Recursive
     * @param array $elements
     * @param integer|null $parentId
     * @return array id
     */
    public function getTeamIdRecursive(array $elements, $parentId = null) {
        $ids = array();
        foreach ($elements as $element)
        {
            if ($element['parent_id'] == $parentId)
            {
                $children = $this->getTeamIdRecursive($elements, $element['id']);
                $ids[$element['id']] = $element['id'];
                if ($children)
                {
                    $ids[$element['id']] = $children;
                }
            }
        }
        return $ids;
    }

    /**
     * return key array of input array
     * @param array input array
     * @return array of keys
     */
    static function getKeyArray(array $array)
    {
        $keys = array();

        foreach ($array as $key => $value) {
            $keys[] = $key;

            if (is_array($value)) {
                $keys = array_merge($keys, self::getKeyArray($value));
            }
        }

        return $keys;
    }

    /**
     * Singleton instance
     *
     * @return \self
     */
    public static function getInstance()
    {
        if (! isset(self::$instance)) {
            self::$instance = new static;
        }
        return self::$instance;
    }
}
