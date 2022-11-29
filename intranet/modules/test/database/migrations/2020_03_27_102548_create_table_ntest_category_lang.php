<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Rikkei\Test\Models\Category;

class CreateTableNtestCategoryLang extends Migration
{
    protected $tbl = 'ntest_category_lang';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->tbl)) {
            return;
        }
        Schema::create($this->tbl, function (Blueprint $table) {
            $table->unsignedBigInteger('cat_id');
            $table->string('lang_code', 2);
            $table->string('name')->nullable();
            $table->primary(['cat_id', 'lang_code']);
            $table->foreign('cat_id')->references('id')->on('ntest_categories')
                    ->onDelete('cascade');
        });

        $allCats = Category::all();
        DB::beginTransaction();
        try {
            $dataCatLangs = [];
            $dfLang = Rikkei\Core\View\CoreLang::DEFAULT_LANG;
            if (!$allCats->isEmpty()) {
                foreach ($allCats as $cat) {
                    $dataCatLangs[] = [
                        'cat_id' => $cat->id,
                        'lang_code' => $dfLang,
                        'name' => $cat->name,
                    ];
                }
            }
            if ($dataCatLangs) {
                DB::table($this->tbl)->insert($dataCatLangs);
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            throw $ex;
        }

        Schema::table('ntest_categories', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
