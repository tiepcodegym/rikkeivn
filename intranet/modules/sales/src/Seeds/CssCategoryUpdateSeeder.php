<?php
namespace Rikkei\Sales\Seeds;

use DB;

class CssCategoryUpdateSeeder extends \Rikkei\Core\Seeds\CoreSeeder
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
        DB::beginTransaction();
        try {
            DB::table('css_category')->where('id',3)
                ->update(['name' => 'プロジェクトの品質']);
        
            DB::table('css_category')->where('id',4)
                    ->update(['name' => 'プロジェクト管理', 'show_pm_name' => 1]);

            DB::table('css_category')->where('id',5)
                    ->update(['name' => 'ブリッジSEの評価', 'show_brse_name' => 1]);

            DB::table('css_category')->where('id',6)
                    ->update(['name' => '要求分析工程について']);

            DB::table('css_category')->where('id',7)
                    ->update(['name' => '設計工程について']);

            DB::table('css_category')->where('id',8)
                    ->update(['name' => 'コーディング工程について']);

            DB::table('css_category')->where('id',9)
                    ->update(['name' => 'プロジェクトのテスト工程について']);

            DB::table('css_category')->where('id',10)
                    ->update(['name' => 'プロジェクトのお客様サポートについて']);

            DB::table('css_category')->where('id',11)
                    ->update(['name' => 'メンバーの能力']);

            DB::table('css_category')->where('id',12)
                    ->update(['name' => 'サービスの品質']);

            DB::table('css_category')->where('id',13)
                    ->update(['name' => '日本語のコミュニケーション能力について', 'show_brse_name' => 1]);

            DB::table('css_category')->where('id',14)
                    ->update(['name' => 'OSDCの責任者について', 'show_pm_name' => 1]);

            DB::table('css_category')->where('id',15)
                    ->update(['name' => '能力/スキル']);

            DB::table('css_category')->where('id',16)
                    ->update(['name' => '作業の成果']);

            DB::table('css_category')->where('id',17)
                    ->update(['name' => '責任感']);

            DB::table('css_category')->where('id',18)
                    ->update(['name' => '労働規律']);

            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
