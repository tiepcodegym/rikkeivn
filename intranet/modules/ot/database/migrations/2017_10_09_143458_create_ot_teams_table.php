<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOtTeamsTable extends Migration
{
    private $table = 'ot_teams';

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

        Schema::create($this->table, function(Blueprint $table) {
            $table->unsignedInteger('register_id');
            $table->unsignedInteger('team_id');
            $table->unsignedInteger('role_id');

            $table->primary(['register_id', 'team_id']);
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
