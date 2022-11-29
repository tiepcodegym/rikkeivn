<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableAssetsRequestDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets_request_details', function (Blueprint $table) {
            $table->dropForeign('assets_request_details_item_type_id_foreign');
            $table->dropForeign('assets_request_details_request_id_foreign');
            $table->dropPrimary(['request_id', 'item_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assets_request_details', function (Blueprint $table) {
            
        });
    }
}
