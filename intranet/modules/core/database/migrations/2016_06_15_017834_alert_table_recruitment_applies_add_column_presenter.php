<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableRecruitmentAppliesAddColumnPresenter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('recruitment_applies') || Schema::hasColumn('recruitment_applies', 'presenter_id')) {
            return;
        }
        Schema::table('recruitment_applies', function (Blueprint $table) {
            $table->unsignedInteger('presenter_id')->nullable();
            
            $table->index('presenter_id');
            $table->foreign('presenter_id')
                ->references('id')
                ->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('recruitment_applies');
    }
}
