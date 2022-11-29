<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddSomeColumnInTasksTable extends Migration
{
    private $table='tasks';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }
        Schema::table($this->table, function (Blueprint $table) {
            $table->tinyInteger('process')->nullable();
            $table->string('label')->nullable();
            $table->text('correction')->nullable();
            $table->text('corrective_action')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }
        Schema::table($this->table, function (Blueprint $table) {
            $table->dropColumn('process');
            $table->dropColumn('label');
            $table->dropColumn('correction');
            $table->dropColumn('corrective_action');
        });
    }
}
