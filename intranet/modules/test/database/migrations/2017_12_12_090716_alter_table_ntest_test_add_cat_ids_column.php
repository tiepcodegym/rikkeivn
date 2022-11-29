<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterTableNtestTestAddCatIdsColumn extends Migration
{
    protected $tbl = 'ntest_tests';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl)) {
            return;
        }
        
        Schema::table($this->tbl, function (Blueprint $table) {
            if (!Schema::hasColumn($this->tbl, 'question_cat_ids')) {
                $table->text('question_cat_ids')->nullable()->after('created_by');
            }
            if (!Schema::hasColumn($this->tbl, 'display_option')) {
                $table->text('display_option')->nullable()->after('created_by');
            }
        });
        
        DB::statement('ALTER TABLE '. $this->tbl .' MODIFY COLUMN updated_at timestamp AFTER question_cat_ids');
        DB::statement('ALTER TABLE '. $this->tbl .' MODIFY COLUMN created_at timestamp AFTER question_cat_ids');
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
