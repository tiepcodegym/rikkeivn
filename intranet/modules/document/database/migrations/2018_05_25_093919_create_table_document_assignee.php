<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDocumentAssignee extends Migration
{
    protected $tbl = 'doc_assignee';

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
            $table->unsignedInteger('doc_id');
            $table->unsignedInteger('employee_id');
            $table->tinyInteger('type')->default(1);
            $table->tinyInteger('status')->default(1)->nullable();
            $table->primary(['doc_id', 'employee_id', 'type']);
            $table->foreign('doc_id')
                    ->references('id')
                    ->on('documents')
                    ->onDelete('cascade');
            $table->foreign('employee_id')
                    ->references('id')
                    ->on('employees')
                    ->onDelete('cascade');
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
