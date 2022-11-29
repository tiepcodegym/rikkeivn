<?php
namespace Rikkei\Sales\Seeds;

use DB;
use Carbon\Carbon;
use Rikkei\Sales\Model\Css;
use Rikkei\Sales\Model\CssResult;
use Rikkei\Sales\Model\CssCategory;
use Rikkei\Sales\Model\CssQuestion;

class CssUpdateTemplateDefaultJapanV2Seeder extends \Rikkei\Core\Seeds\CoreSeeder
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

            $cssCateJaId = CssCategory::where("name", "日本語でのコミュニケーション")->where("css_id", 0)->where("code", 1)->where("lang_id", Css::JAP_LANG)->first();
            if ($cssCateJaId) {
                CssQuestion::where("category_id", $cssCateJaId->id)->where("content", "メール日本語訳の品質はいかがでしたか。")->update([
                    "content" => "メール/チャットソフトウェアでの日本語翻訳質はいかがでしたか。"
                ]);
                CssQuestion::where("category_id", $cssCateJaId->id)->where("content", "会議での日本語通訳の質はいかがでしたか。（電話会議、TV会議、チャット等）")->update([
                    "content" => "会議での日本語通訳質はいかがでしたか。（電話会議、TV会議)"
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
