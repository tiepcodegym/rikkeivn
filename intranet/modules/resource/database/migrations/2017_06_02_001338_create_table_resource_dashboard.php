<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableResourceDashboard extends Migration
{
    protected $tbl = 'resource_dashboard';
    
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
           $table->increments('id');
           $table->text('data');
           $table->unsignedTinyInteger('type');
           $table->unsignedInteger('team_id')->nullable();
           $table->timestamps();
           $table->foreign('team_id')->references('id')->on('teams')
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
