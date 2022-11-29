<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeAttachFiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('employee_attach_files')) {
            return true;
        }
        Schema::create('employee_attach_files', function (Blueprint $table) {
            $table->unsignedInteger('attach_id'); // foreign emloyee attach
            $table->string('file_id', 20); // random text for attach
            $table->string('file_name')->nullable(); // name of file origin
            $table->string('path')->nullable(); // path of file rename
            $table->string('type')->nullable();
            $table->float('file_size')->nullable();
            $table->dateTime('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('employee_attach_files')) {
            return true;
        }
        Schema::drop('employee_attach_files');
    }
}
