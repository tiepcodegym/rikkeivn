<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableManageFileText extends Migration
{
    private $table = 'manage_file_text';

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
        Schema::create($this->table, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('type')->comment = 'loại sổ, 1- cv đến, 2-cv đi';
            $table->integer('number_go')->nullable()->comment = 'số đi';
            $table->integer('number_to')->nullable()->comment = 'số đến';
            $table->string('code_file', 100)->comment = 'số ký hiệu đơn vị';
            $table->integer('type_file')->comment = 'loại văn bản';
            $table->string('file_from', 255)->nullable()->comment = 'nơi nhận';
            $table->longtext('quote_text')->comment = 'trích yếu văn bản';
            $table->longtext('note_text')->nullable()->comment = 'Ghi chú';
            $table->string('file_content', 255)->nullable()->comment = 'tệp nội dung';
            $table->string('file_to', 255)->nullable()->comment = 'nơi gửi';
            $table->string('save_file', 255)->nullable()->comment = 'nơi lưu bản gốc';
            $table->dateTime('date_file')->comment = 'ngày văn bản';
            $table->integer('tick')->nullable()->comment = 'đã ký';
            $table->integer('status')->comment = '1-đã vào sổ,null-đang chờ xử lý';
            $table->dateTime('date_released')->nullable()->comment = 'ngày phát hành';
            $table->dateTime('date_file_send')->nullable()->comment = 'ngày văn bản đến';
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('team_id');
            $table->unsignedInteger('signer')->nullable()->comment = 'người ký';
            $table->dateTime('deleted_at')->nullable();
            $table->foreign('created_by')->references('id')->on('employees');
            $table->foreign('team_id')->references('id')->on('teams');
            $table->foreign('signer')->references('id')->on('employees');
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
