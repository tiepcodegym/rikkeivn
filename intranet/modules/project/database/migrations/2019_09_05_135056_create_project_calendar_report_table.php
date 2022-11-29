<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectCalendarReportTable extends Migration
{
    protected $table = 'project_calendar_report';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table)) {
            Schema::create($this->table, function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('project_id');
                $table->date('date')->comment('Ngày report');
                $table->string('title', 50)->comment('Title report');
                $table->string('description', 100)->comment('Nội dung report');
                $table->tinyInteger('signal')->default(1)->comment('1: Fine 2: Usually 3: Bad');
                $table->timestamps();

                // Index Unique
                $table->unique(['project_id', 'date']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->table);
    }
}
