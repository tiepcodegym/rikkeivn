<?php
namespace Rikkei\Core\Seeds;

use Rikkei\Core\Model\CoreConfigData;

class SettingAutoApproveCommentSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return true;
        }
        $value = CoreConfigData::AUTO_APPROVE;
        $key = CoreConfigData::AUTO_APPROVE_COMMNENT_KEY;
        $item = CoreConfigData::getItem($key);
        if (!$item) {
            $item = new CoreConfigData();
            $item->key = $key;
        }
        $item->value = $value;
        $item->save();
        \Log::info($item);
        $this->insertSeedMigrate();
    }
}
