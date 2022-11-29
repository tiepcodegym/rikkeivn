<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProposesTable extends Migration
{
    protected $tbl = 'proposes';

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
            $table->unsignedInteger('cat_id');
            $table->string('title');
            $table->text('proposed_content');
            $table->unsignedInteger('created_by')->comment('id nhân viên góp ý');
            $table->tinyInteger('status')->default(1);
            $table->text('answer_content')->nullable()->comment('Nội dung trả lời góp ý');
            $table->unsignedInteger('updated_by')->nullable()->comment('id nhân viên viết câu trả lời');
            $table->tinyInteger('feedback')->default(1)->comment('1: chua phan hoi, 2 phan hoi, 3 khong phan hoi');
            $table->tinyInteger('level')->default(1)->comment('1: trạng thái -,  2: ghi nhan, 3 huu ich, 4 rat huu ich');
            $table->dateTime('created_at_answer')->nullable()->comment('ngày trả lời góp ý');
            $table->dateTime('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('cat_id')->references('id')->on('proposed_categories');
            $table->foreign('created_by')->references('id')->on('employees');
            $table->foreign('updated_by')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop($this->tbl);
    }
}
