<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeSortOrderColumnReasonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leave_day_reasons', function (Blueprint $table) {
           $table->integer('sort_order')->default(1)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leave_day_reasons', function (Blueprint $table) {
            $table->integer('sort_order')->nullable()->change();
        });
    }
}
