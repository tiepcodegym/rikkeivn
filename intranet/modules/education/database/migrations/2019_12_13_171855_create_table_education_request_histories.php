<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEducationRequestHistories extends Migration
{
    protected $table = 'education_request_histories';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->table)) {
            return;
        }
        Schema::create($this->table, function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('education_request_id');
            $table->unsignedInteger('hr_id');
            $table->tinyInteger('status')->comment('1: closed|2: pending| 3: requesting| 4: opening| 5: reject');
            $table->text('description');
            $table->timestamps();
            $table->foreign('education_request_id')->references('id')->on('education_requests')->onDelete('cascade');
            $table->foreign('hr_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop($this->table);
    }
}
