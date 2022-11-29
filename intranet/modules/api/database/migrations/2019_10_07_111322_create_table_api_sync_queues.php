<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableApiSyncQueues extends Migration
{
    protected $tbl = 'api_sync_queues';

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
            $table->string('api_url');
            $table->string('method', 10)->default('post');
            $table->string('data')->nullable();
            $table->boolean('is_auth')->default(1);
            $table->string('type', 16)->nullable();
            $table->unsignedInteger('employee_id')->nullable();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('schedule')->nullable();
            $table->text('error')->nullable();
            $table->unsignedInteger('called_by')->nullable();
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
