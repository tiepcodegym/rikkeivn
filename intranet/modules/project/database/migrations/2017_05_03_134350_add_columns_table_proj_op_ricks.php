<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsTableProjOpRicks extends Migration
{
    protected $tbl = 'proj_op_ricks';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->unsignedTinyInteger('type')->comment("Type of risk"); 
            $table->text('description'); 
            $table->string('code', 50)->nullable(); 
            $table->unsignedTinyInteger('level_important'); 
            $table->text('weakness');
            $table->text('solution_using')->nullable()->comment("Solution are using");
            $table->unsignedTinyInteger('posibility_using')->nullable()->comment("risk occur possibility of solution are using");
            $table->unsignedTinyInteger('impact_using')->nullable()->comment("impact of solution are using");
            $table->float('value_using')->comment('risk value')->nullable()->comment("risk value of solution are using");
            $table->unsignedTinyInteger('handling_method_using')->nullable()->comment("handling method risk are using");
            $table->text('solution_suggest')->nullable()->comment("Solution suggest");
            $table->unsignedTinyInteger('possibility_suggest')->nullable()->comment("risk occur possibility of solution suggest");
            $table->unsignedTinyInteger('impact_suggest')->nullable()->comment('impact of solution suggest');
            $table->float('value_suggest')->nullable()->comment('risk value of solution suggest');
            $table->unsignedTinyInteger('risk_acceptance_criteria')->nullable()->comment("risk acceptance criteria of suggest");
            $table->unsignedTinyInteger('handling_method_suggest')->nullable()->comment("handling method risk suggest");
            $table->text('acceptance_reason')->nullable()->comment("risk acceptance reason");
            $table->unsignedInteger("owner")->nullable()->comment("risk owner");
            $table->date('finish_date')->nullable();
            $table->unsignedInteger("performer")->nullable();
            $table->text('evidence')->nullable()->comment('EVIDENCE OF IMPLEMENTING RISK ASSISTANCE');
            $table->boolean('result')->nullable();
            $table->date('test_date')->nullable();
            $table->unsignedInteger('tester')->nullable();
            $table->foreign('owner')->references('id')->on('employees');
            $table->foreign('performer')->references('id')->on('employees');
            $table->foreign('tester')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable($this->tbl)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->dropColumn('type'); 
            $table->dropColumn('description'); 
            $table->dropColumn('code'); 
            $table->dropColumn('level_important'); 
            $table->dropColumn('weakness'); 
            $table->dropColumn('solution_using'); 
            $table->dropColumn('posibility_using'); 
            $table->dropColumn('impact_using'); 
            $table->dropColumn('value_using'); 
            $table->dropColumn('handling_method_using'); 
            $table->dropColumn('solution_suggest'); 
            $table->dropColumn('possibility_suggest'); 
            $table->dropColumn('impact_suggest'); 
            $table->dropColumn('value_suggest'); 
            $table->dropColumn('risk_acceptance_criteria'); 
            $table->dropColumn('handling_method_suggest'); 
            $table->dropColumn('acceptance_reason'); 
            $table->dropColumn('owner'); 
            $table->dropColumn('finish_date'); 
            $table->dropColumn('performer'); 
            $table->dropColumn('evidence'); 
            $table->dropColumn('result'); 
            $table->dropColumn('test_date'); 
            $table->dropColumn('tester'); 
        });
    }
}
