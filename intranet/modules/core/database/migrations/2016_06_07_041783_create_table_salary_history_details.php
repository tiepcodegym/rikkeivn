<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSalaryHistoryDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('salary_history_details')) {
            return;
        }
        Schema::create('salary_history_details', function (Blueprint $table) {
            $table->unsignedBigInteger('history_id');
            $table->unsignedInteger('salary_type_id');
            $table->integer('amount');
            
            $table->primary(['history_id', 'salary_type_id']);
            $table->index('salary_type_id');
            $table->foreign('history_id')
                ->references('id')
                ->on('salary_histories');
            $table->foreign('salary_type_id')
                ->references('id')
                ->on('salary_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('salary_history_details');
    }
}
