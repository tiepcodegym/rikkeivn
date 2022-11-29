<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTitleColumnTableEducationRequests extends Migration
{
    protected $table = 'education_requests';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->table, function ($table) {
            $table->string('title', 100)->change();
            $table->dateTime('start_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->table, function ($table) {
            $table->string('title')->change();
            $table->dateTime('start_date')->change();
        });
    }
}
