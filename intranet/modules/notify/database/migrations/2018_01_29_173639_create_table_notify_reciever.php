<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableNotifyReciever extends Migration
{
    protected $tbl = 'notify_reciever';
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
            $table->unsignedBigInteger('notify_id');
            $table->unsignedInteger('reciever_id');
            $table->timestamp('read_at')->nullable();
            $table->foreign('reciever_id')
                    ->references('id')
                    ->on('employees')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            $table->foreign('notify_id')
                    ->references('id')
                    ->on('notifications')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            $table->primary(['notify_id', 'reciever_id']);
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
