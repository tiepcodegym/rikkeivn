<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Welfare\Model\Event;

class CreateTableWelfare extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('welfares')) {
            return;
        }
        Schema::create('welfares', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('welfare_group_id')->unsigned();
            $table->dateTime('start_at_exec');
            $table->dateTime('end_at_exec');
            $table->dateTime('start_at_register');
            $table->dateTime('end_at_register');
            $table->text('description');
            $table->integer('wel_purpose_id')->unsigned();
            $table->integer('wel_form_imp_id')->unsigned();
            $table->text('address');
            $table->integer('join_number_plan');
            $table->integer('join_number_exec');
            $table->tinyInteger('status');
            $table->text('participant_desc');
            $table->boolean('is_same_fee')->default(0);
            $table->boolean('is_register_online')->default(Event::IS_REGISTER_ONLINE);
            $table->boolean('is_allow_attachments')->default(Event::IS_ATTACHED);
            $table->boolean('is_send_mail_auto')->default(0);

            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->unsignedInteger('created_by')->nullable();

            $table->foreign('welfare_group_id')->references('id')->on('welfare_groups');
            $table->foreign('wel_purpose_id')->references('id')->on('wel_purposes');
            $table->foreign('wel_form_imp_id')->references('id')->on('wel_form_implements');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('welfares');
    }
}
