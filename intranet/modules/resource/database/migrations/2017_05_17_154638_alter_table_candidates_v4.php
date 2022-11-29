<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCandidatesV4 extends Migration
{
    protected $tbl = 'candidates';
    
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
        if (!Schema::hasColumn($this->tbl, 'test_option_type_id')) {
            return;
        }
        DB::beginTransaction();
        try {
            //get old data
            $oldData = DB::table($this->tbl)
                    ->select('id', 'test_option_type_id')
                    ->get();
            
            Schema::table($this->tbl, function (Blueprint $table) use ($oldData) {
                $table->dropForeign('candidates_test_option_type_id_foreign');
                $table->dropColumn('test_option_type_id');
                if (!Schema::hasColumn($this->tbl, 'test_option_type_ids')) {
                    $table->string('test_option_type_ids')->nullable();
                }
            });
            //backup old data
            foreach ($oldData as $data) {
                if ($data->test_option_type_id) {
                    DB::table($this->tbl)
                            ->where('id', $data->id)
                            ->update(['test_option_type_ids' => serialize([$data->test_option_type_id])]);
                }
            }
            DB::commit();
        } catch (\Exception $ex){
            DB::rollback();
            throw $ex;
        }
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
