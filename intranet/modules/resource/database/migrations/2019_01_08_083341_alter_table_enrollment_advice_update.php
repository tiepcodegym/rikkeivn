<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableEnrollmentAdviceUpdate extends Migration
{
    protected $tbl = 'enrollment_advice';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->string('phone')->nullable()->change();
            $table->smallInteger('status')->default(2)->change();
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
