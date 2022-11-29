<?php
namespace Rikkei\Team\Seeds;

use DB;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\Role;
use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\EmplCvAttrValueText;

class EmplAttrRemoveSeeder extends CoreSeeder
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
        $tbl = EmplCvAttrValueText::getTableName();
        $fields = [
            'field_dev',
        ];
        DB::beginTransaction();
        try {
            foreach ($fields as $field) {
                DB::table($tbl)
                    ->where('code', 'LIKE', $field.'_%')
                    ->delete();
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
