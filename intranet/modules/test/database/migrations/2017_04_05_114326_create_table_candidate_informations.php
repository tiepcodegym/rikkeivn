<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCandidateInformations extends Migration
{
    protected $tbl = 'candidate_informations';
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
        $fields = ['full_name', 'birth', 'identify', 'home_town', 'phone_number', 'position', 'salary', 'start_time', 'had_worked', 'hear_recruitment', 'relatives'];
        Schema::create($this->tbl, function (Blueprint $table) use ($fields) {
           $table->increments('id');
           foreach ($fields as $field) {
               $table->string($field);
           }
           $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tbl);
    }
}
