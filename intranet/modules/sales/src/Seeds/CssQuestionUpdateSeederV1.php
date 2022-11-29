<?php
namespace Rikkei\Sales\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use DB;

class CssQuestionUpdateSeederV1 extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }
        DB::table('css_question')->where('id', 10)
                ->update(['content' => '不具合の対応についていかがでしたか。（影響範囲の調査、対応期間など）']);

        $this->insertSeedMigrate();
    }
}
