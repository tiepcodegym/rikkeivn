<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRequestOpporCvMember extends Migration
{
    protected $tbl = 'request_oppor_cv_member';

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
            $table->unsignedInteger('req_oppor_id');
            $table->text('note')->nullable();
            $table->unsignedInteger('created_by');
            $table->timestamps();
            $table->foreign('req_oppor_id')
                    ->references('id')
                    ->on('request_opportunities')
                    ->onDelete('cascade');
            $table->foreign('created_by')
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
