<?php
namespace Rikkei\Help\Seeds;

use Rikkei\Help\Model\Help;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Seeds\CoreSeeder;

class HelpItemUpdateSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(3)) {
            return true;
        }
        $dataFilePath = RIKKEI_HELP_PATH .'data-sample' . DIRECTORY_SEPARATOR . 
            'help-items-update.php';
        if (!file_exists($dataFilePath)) {
            return;
        }
        $dataDemo = require $dataFilePath;
        if (!$dataDemo || !count($dataDemo)) {
            return;
        }
        DB::beginTransaction();        
        try {
            $this->createHelpsRecurive($dataDemo, null, 0);
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    protected function createHelpsRecurive($data, $parentId, $sortOrder)
    {
        foreach ($data as $key => $item) {
            $dataChild = null;
            if (isset($item['child'] ) && count($item['child']) > 0) {
                $dataChild = $item['child'];
                unset($item['child']);         
            }
            $dataItem = [];
            $dataItem = [
                'parent' => $parentId,
                'title' => $key,
                'content' => '',
                'active' => 1,
                'order' => $sortOrder
            ];
            $helpItem = Help::where('title', $item['title'])
                ->where('parent', $parentId)
                ->first();
            if (isset($item['content'])) { // update or create self
                $dataItem = array_merge($dataItem, $item);
                if (!$helpItem) {
                    $helpItem = new Help();
                }
                $helpItem->setData($dataItem);
                $helpItem->save();
            } else { // continue to update child
                if (!$helpItem) { // not have parent -> continue
                    continue;
                }
            }
            if ($dataChild) {
                $this->createHelpsRecurive($dataChild, $helpItem->id, 0);
            }
            $sortOrder++;
        }
    }
}
