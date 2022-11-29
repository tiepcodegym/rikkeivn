<?php
namespace Rikkei\Team\Seeds;

use DB;
use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\EmplCvAttrValue;

class ProfileEnglishSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(1)) {
            return true;
        }
        $collection = EmplCvAttrValue::select(['employee_id', 'code', 'value'])
            ->where('code', 'lang_en_level')
            ->where('value', 'like', 'TOIEC%')
            ->get();
        if (!count($collection)) {
            $this->insertSeedMigrate();
            return true;
        }
        DB::beginTransaction();
        try {
            foreach ($collection as $item) {
                $valueChange = preg_replace('/^TOIEC/', 'TOEIC', $item->value);
                EmplCvAttrValue::where('code', $item->code)
                    ->where('employee_id', $item->employee_id)
                    ->update([
                        'value' => $valueChange
                    ]);
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
