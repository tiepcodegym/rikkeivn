<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableOperationOverviewTable extends Migration
{
    private $table = 'operation_overview';
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
            $table->string('base');
            $table->string('members');
            $table->string('month');
            $table->string('onsite');
            $table->string('osdc');
            $table->string('project');
            $table->integer('team_id');
            $table->string('branch_code');
            $table->boolean('is_collapse')->default(false);
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
        Schema::drop($this->table);
    }
}
