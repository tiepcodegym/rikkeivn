<?php
namespace Rikkei\Help\Seeds;

use Rikkei\Help\Model\Help;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Seeds\CoreSeeder;

class HelpItemsSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(2)) {
            return true;
        }
        $dataFilePath = RIKKEI_HELP_PATH . 'config' . DIRECTORY_SEPARATOR .  'helpItems.php';
        if (! file_exists($dataFilePath)) {
            return;
        }
        $dataDemo = require $dataFilePath;
        if (! $dataDemo || ! count($dataDemo)) {
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
                'order' => $sortOrder,
                'created_by' => 1
            ];
            if (isset($item['title']) && $item['title']) {
                $dataItem['title'] = $item['title'];
            }       
            if (isset($item['active']) && $item['active']) {
                $dataItem['active'] = $item['active'];
            }
            if (isset($item['content']) && $item['content']) {
                $dataItem['content'] = $item['content'];
            }
        
            $helpItem = Help::where('title', $dataItem['title'])
                ->where('parent', $parentId)
                ->first();
            if (!$helpItem) {
                $helpItem = new Help();               
                $helpItem->setData($dataItem);
                $helpItem->save();
            }
            if ($dataChild) {
                $this->createHelpsRecurive($dataChild, $helpItem->id, 0);
            }
            $sortOrder++;
        }
    }
}
