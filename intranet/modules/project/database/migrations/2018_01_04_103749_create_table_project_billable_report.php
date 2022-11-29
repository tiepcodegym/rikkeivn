<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableProjectBillableReport extends Migration
{
    protected $tbl = 'proj_billable_report';

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
            $table->string('customer_company')->nullable();
            $table->string('project_name');
            $table->string('project_type', 20);
            $table->unsignedInteger('team_id');
            $table->string('estimated', 64);
            $table->string('member');
            $table->string('effort', 10)->nullable();
            $table->string('start_at', 15)->nullable();
            $table->string('end_at', 15)->nullable();
            $table->string('status', 64);
            $table->string('released_date', 15)->nullable();
            $table->float('price')->nullable();
            $table->string('price_unit', 3)->default('USD');
            $table->string('saleman')->nullable();
            $table->timestamps();
            $table->index(['project_name', 'member', 'start_at', 'end_at']);
            $table->foreign('team_id')
                    ->references('id')
                    ->on('teams')
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
