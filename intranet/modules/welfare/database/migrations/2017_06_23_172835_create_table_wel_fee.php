<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableWelFee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('wel_fee')) {
            return;
        }
        Schema::create('wel_fee', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('wel_id')->unsigned();

            $table->float('empl_trial_fee', 15, 2);
            $table->float('empl_trial_company_fee', 15, 2);
            $table->integer('empl_trial_number');
            $table->date('empl_offical_after_date');
            $table->float('empl_offical_fee', 15, 2);
            $table->float('empl_offical_company_fee', 15, 2);
            $table->integer('empl_offical_number');
            $table->float('intership_fee', 15, 2);
            $table->float('intership_company_fee', 15, 2);
            $table->integer('intership_number');
            $table->float('attachments_first_fee', 15, 2);
            $table->float('attachments_first_company_fee', 15, 2);
            $table->integer('attachments_first_number');
            $table->float('attachments_second_fee', 15, 2);
            $table->float('attachments_second_company_fee', 15, 2);
            $table->integer('attachments_second_number');
            $table->float('fee_total', 15, 2);

            $table->float('empl_trial_fee_actual', 15, 2)->nullable();
            $table->float('empl_trial_company_fee_actual', 15, 2)->nullable();
            $table->integer('empl_trial_number_actual')->nullable();
            $table->float('empl_offical_fee_actual', 15, 2)->nullable();
            $table->float('empl_offical_company_fee_actual', 15, 2)->nullable();
            $table->integer('empl_offical_number_actual')->nullable();
            $table->float('intership_fee_actual', 15, 2)->nullable();
            $table->float('intership_company_fee_actual', 15, 2)->nullable();
            $table->integer('intership_number_actual')->nullable();
            $table->float('attachments_first_fee_actual', 15, 2)->nullable();
            $table->float('attachments_first_company_fee_actual', 15, 2)->nullable();
            $table->integer('attachments_first_number_actual')->nullable();
            $table->float('attachments_second_fee_actual', 15, 2)->nullable();
            $table->float('attachments_second_company_fee_actual', 15, 2)->nullable();
            $table->integer('attachments_second_number_actual')->nullable();
            $table->float('fee_total_actual', 15, 2)->nullable();

            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->unsignedInteger('created_by')->nullable();

            $table->foreign('wel_id')->references('id')->on('welfares');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wel_fee');
    }
}
