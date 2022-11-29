<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCssCommentFeedback extends Migration
{
    protected $table = 'css_comments';
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
            $table->text('content')->nullable();
            $table->unsignedInteger('created_by');
            $table->foreign('created_by')
                    ->references('id')
                    ->on('employees');
            $table->unsignedInteger('css_result_id');
            $table->foreign('css_result_id')
                    ->references('id')
                    ->on('css_result');
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
        if (Schema::hasTable($this->table)) {
            Schema::drop($this->table);
        }
    }
}
