<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDerivedExpensesTable extends Migration
{
    private $table = 'devices_expenses';
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
            $table->date('time');
            $table->float('amount', 15,3);
            $table->text('description');
            $table->unsignedInteger('project_id');
            $table->smallInteger('status');
            $table->unsignedInteger('parent_id')->nullable();
            $table->unsignedInteger('task_id')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();

            $table->foreign('project_id')
                ->references('id')
                ->on('projs');
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
