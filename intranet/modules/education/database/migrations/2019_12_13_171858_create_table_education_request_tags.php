<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEducationRequestTags extends Migration
{
    protected $table = 'education_request_tags';
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
            $table->unsignedInteger('education_request_id');
            $table->unsignedInteger('tag_id');
            $table->foreign('education_request_id')->references('id')->on('education_requests')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('education_tags')->onDelete('cascade');
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
