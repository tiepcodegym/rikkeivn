<?php

namespace Rikkei\Api\Helper;

use Rikkei\Api\Helper\Base as BaseHelper;
use Rikkei\Team\Model\Role as RoleModel;

/**
 * Description of Contact
 *
 * @author lamnv
 */
class Role extends BaseHelper
{
    public function __construct() {
        $this->model = RoleModel::class;
    }

    /**
     * get roles list
     * @param array $data
     * @return array
     */
    public function getList($data = [])
    {
        return parent::getList(array_merge([
            'select' => [
                'id',
                'role as name',
            ],
            's' => null,
            'page' => 1,
            'per_page' => -1,
            'fields_search' => ['role'],
            'orderby' => 'id',
            'order' => 'asc',
            'where' => [
                [
                    'field' => 'special_flg',
                    'compare' => '=',
                    'value' => 1
                ]
            ]
        ], array_filter($data)));
    }

}
