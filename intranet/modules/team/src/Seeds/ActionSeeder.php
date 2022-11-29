<?php
namespace Rikkei\Team\Seeds;

use Rikkei\Team\Model\Action;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Seeds\CoreSeeder;

class ActionSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(597)) {
            return true;
        }
        $dataFilePath = RIKKEI_TEAM_PATH . 'data-sample' . DIRECTORY_SEPARATOR . 'seed' .
                DIRECTORY_SEPARATOR .  'acl.php';
        if (! file_exists($dataFilePath)) {
            return;
        }
        $dataDemo = require $dataFilePath;
        if (! $dataDemo || ! count($dataDemo)) {
            return;
        }
        DB::beginTransaction();
        try {
            $this->createActionsRecurive($dataDemo, null, 0);
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * create action acl recurive
     *
     * @param array $data
     * @param int $parentId
     * @param int $sortOrder
     */
    protected function createActionsRecurive($data, $parentId, $sortOrder)
    {
        foreach ($data as $key => $item) {
            $dataChild = null;
            if (isset($item['child'] ) && count($item['child']) > 0) {
                $dataChild = $item['child'];
                unset($item['child']);
                if (isset($item['routes'])) {
                    unset($item['routes']);
                }
            }
            $dataItem = [];
            $dataItem = [
                'parent_id' => $parentId,
                'route' => '',
                'name' => $key,
                'description' => '',
                'sort_order' => $sortOrder,
            ];
            if (isset($item['label']) && $item['label']) {
                $dataItem['description'] = trim($item['label']);
            }
            $actionItem = Action::where('name', $dataItem['name'])
                ->where('parent_id', $parentId)
                ->first();

            if (! $actionItem) {
                $actionItem = new Action();
                //add route flag
                $actionItem->setData($dataItem);
                $actionItem->save();
            } else {
                $actionItem->description = $dataItem['description'];
                $actionItem->save();
            }
            //add route action
            if (isset($item['routes']) && count($item['routes'])) {
                foreach ($item['routes'] as $keyRotue => $route) {
                    $dataItemRoute = [
                        'parent_id' => $actionItem->id,
                        'route' => $route,
                        'name' => $key . '-route.child.' . $route,
                        'description' => '',
                        'sort_order' => $keyRotue,
                    ];
                    $actionRouteItem = Action::where('name', $dataItemRoute['name'])
                        ->first();
                    if (! $actionRouteItem) {
                        $actionRouteItem = new Action();
                        $actionRouteItem = $actionRouteItem->setData($dataItemRoute);
                        $actionRouteItem->save();
                    }
                }
            }
            if ($dataChild) {
                $this->createActionsRecurive($dataChild, $actionItem->id, 0);
            }
            $sortOrder++;
        }
    }
}
