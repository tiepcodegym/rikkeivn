<?php

namespace Rikkei\Assets\View;

use Rikkei\Team\View\Permission;

class AssetPermission
{
    /**
     * Permission for view list
     * @return [boolean]
     */
    public static function viewListPermision()
    {
        return Permission::getInstance()->isAllow('asset::asset.index');
    }

    /**
     * Permission for view detail
     * @return [boolean]
     */
    public static function viewDetailPermision()
    {
        return Permission::getInstance()->isAllow('asset::asset.view');
    }

    /**
     * Permission for create and edit
     * @return [boolean]
     */
    public static function createAndEditPermision()
    {
        return Permission::getInstance()->isAllow('asset::asset.add');
    }

    /**
     * Permission for delele
     * @return [boolean]
     */
    public static function deletePermision()
    {
        return Permission::getInstance()->isAllow('asset::asset.delete');
    }

    /**
     * Permission for allocation and retrieval
     * @return [boolean]
     */
    public static function allocationAndRetrievalPermision()
    {
        return Permission::getInstance()->isAllow('asset::asset.asset-allocation');
    }

    /**
     * Permission for approve of lost notification, broken notification, liquidate suggest, repair and maintenance suggest
     * @return [boolean]
     */
    public static function approvePermision()
    {
        return Permission::getInstance()->isAllow('asset::asset.approve');
    }

    /**
     * Permission for report
     * @return [boolean]
     */
    public static function reportPermision()
    {
        return Permission::getInstance()->isAllow('asset::asset.report');
    }
}
