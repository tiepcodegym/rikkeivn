<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobFinesMoneyTable extends Migration
{
    protected $tbl = 'job_fines_money';
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
            $table->unsignedInteger('created_by');
            $table->bigInteger('num')->comment('Số bản ghi thực thi thành công')->default(0);
            $table->bigInteger('total')->comment('Tổng bản ghi cần thực thi')->default(0);
            $table->string('file');
            $table->foreign('created_by')
                ->references('id')
                ->on('employees')
                ->onDelete('cascade')
                ->onUpdate('cascade');
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
        Schema::dropIfExists($this->tbl);
    }
}
