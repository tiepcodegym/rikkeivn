<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSalaryMailSent extends Migration
{
    protected $tbl = 'salary_mail_sent';

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
            $table->unsignedInteger('file_id');
            $table->string('employee_code')->nullable();
            $table->string('email');
            $table->string('fullname')->nullable();
            $table->string('number_sent')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->index(['file_id', 'email']);
            $table->foreign('file_id')
                    ->references('id')
                    ->on('salary_files')
                    ->onUpdate('cascade')
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
