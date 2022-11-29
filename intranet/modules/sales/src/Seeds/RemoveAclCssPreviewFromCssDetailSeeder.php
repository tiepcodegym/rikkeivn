<?php
namespace Rikkei\Sales\Seeds;

use DB;

class RemoveAclCssPreviewFromCssDetailSeeder extends \Rikkei\Core\Seeds\CoreSeeder
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
        DB::table('actions')->whereIn('name', ['view.CSS detail.css-route.child.sales::css.preview', 'edit.detail.css-route.child.sales::css.preview'])->delete();
        $this->insertSeedMigrate();
    }
}
