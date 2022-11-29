<?php

namespace Rikkei\Assets\Model;

use Exception;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetSupplier extends CoreModel
{
    protected $table = 'manage_asset_suppliers';

    protected $fillable = ['code', 'name', 'address', 'phone', 'email', 'website'];
    const TYPE = 'NCC';

    use SoftDeletes;
    /**
     * Get collection to show grid
     * @return type
     */
    public static function getGridData()
    {
        $tblAssetSupplier = self::getTableName();
        $collection = self::select("{$tblAssetSupplier}.id", "{$tblAssetSupplier}.code", "{$tblAssetSupplier}.name", "{$tblAssetSupplier}.address", "{$tblAssetSupplier}.phone", "{$tblAssetSupplier}.email", "{$tblAssetSupplier}.website");
        $pager = Config::getPagerData(null, ['dir' => 'DESC']);
        $collection = $collection->orderBy($pager['order'], $pager['dir']);
        $collection = self::filterGrid($collection, [], null, 'LIKE');
        return self::pagerCollection($collection, $pager['limit'], $pager['page']);
    }

    /**
     * Rewrite save
     * @param array $options
     */
    public function save(array $options = [])
    {
        try {
            parent::save($options);
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Get asset suppliers list
     * @return [array]
     */
    public static function getAssetSuppliersList()
    {
        return self::select('id', 'name')
            ->get();
    }

    /**
     * Count data related to supplier
     * @return [int]
     */
    public static function countDataRelatedToSupplier($supplierId)
    {
        $tblAssetSupplier = self::getTableName();
        $tblAssetItem = AssetItem::getTableName();
        return self::join("{$tblAssetItem}", "{$tblAssetItem}.supplier_id", "=", "{$tblAssetSupplier}.id")
            ->where("{$tblAssetSupplier}.id", $supplierId)
            ->count();
    }

    /**
     * Get max supplier code
     * @return [string]
     */
    public static function getMaxSupplierCode()
    {
        return self::select(\DB::raw('SUBSTRING(code, 4) * 1 AS code_int'))->orderBy('code_int', 'DESC')->withTrashed()->first();
    }

    /**
     * Heading file import
     *
     * @return array
     */
    public static function defineHeadingFile()
    {
        return [
            0 => "ma_ncc",
            1 => "ten_ncc",
            2 => "dia_chi",
            3 => "sdt",
            4 => "email",
            5 => "website",
        ];
    }

    /**
     * Import file supplier
     *
     * @param array $dataRow
     */
    public static function importFile($dataRow)
    {
        $errors = [];
        $dataInsert = [];
        foreach ($dataRow as $key => $row) {
            if (!$row['ma_ncc'] &&
                !$row['ten_ncc'] &&
                !$row['dia_chi']
            ) {
                continue;
            }

            if (!$row['ma_ncc'] ||
                !$row['ten_ncc'] ||
                !$row['dia_chi']
            ) {
                $errors[] = trans('asset::message.Row :row: miss code, name or address supplier', ['row' => $key + 2]);
            }
            $checkCode = AssetSupplier::where('code', $row['ma_ncc'])->first();
            $checkName = AssetSupplier::where('name', $row['ten_ncc'])->first();
            $dataItem = [
                'code' => $row['ma_ncc'],
                'name' => $row['ten_ncc'],
                'address' => $row['dia_chi'],
                'phone' => $row['sdt'],
                'email' => $row['email'],
                'website' => $row['website'],
            ];
            if (!$checkCode && !$checkName) {
                $dataInsert[] = $dataItem;
            } else {
                if ($checkCode) {
                    $checkCode->fill($dataItem);
                    $checkCode->save();
                }
                if ($checkName) {
                    $checkName->fill($dataItem);
                    $checkName->save();
                }
            }
        }

        if (!empty($errors)) {
            return $errors;
        }

        if (!empty($dataInsert)) {
            AssetSupplier::insert($dataInsert);
        }
    }

    /**
     * Check exist asset supplier
     *
     * @param array $inputData
     * @return string
     */
    public static function checkExist($inputData)
    {
        if (!empty($inputData['supplierId'])) {
            return self::where($inputData['name'], $inputData['value'])
                ->where('id', '<>', $inputData['supplierId'])->count() == AssetWarehouse::CHECK_EXIST ? 'false' : 'true';
        } else {
            return self::where($inputData['name'], $inputData['value'])->count() == AssetWarehouse::CHECK_EXIST ? 'false' : 'true';
        }
    }

    /**
     * Get info asset supplier by code
     *
     * @param string $codeSupplier
     * @param array $select
     * @return mixed
     */
    public static function getSupplierByCode($codeSupplier, $select = ['id', 'code', 'name'])
    {
        return self::where('code', $codeSupplier)
            ->select($select)
            ->first();
    }
}
