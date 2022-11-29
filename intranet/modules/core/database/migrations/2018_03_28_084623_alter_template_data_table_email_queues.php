<?php

use Illuminate\Database\Migrations\Migration;

class AlterTemplateDataTableEmailQueues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            ALTER TABLE `email_queues`
            modify `template_data` mediumText
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
