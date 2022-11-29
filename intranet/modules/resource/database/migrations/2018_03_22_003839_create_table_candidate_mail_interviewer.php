<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCandidateMailInterviewer extends Migration
{
    protected $tbl = 'candidate_mail_interviewer';

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
            $table->bigIncrements('id');
            $table->unsignedInteger('interviewer_id');
            $table->unsignedInteger('candidate_id');
            $table->string('title', 500);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->text('description');
            $table->string('room_id');
            $table->timestamps();
            $table->foreign('interviewer_id')->references('id')->on('employees')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            $table->foreign('candidate_id')->references('id')->on('candidates')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
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
