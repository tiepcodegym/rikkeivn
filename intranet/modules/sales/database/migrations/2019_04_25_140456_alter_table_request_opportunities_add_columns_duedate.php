<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableRequestOpportunitiesAddColumnsDuedate extends Migration
{
    protected $tbl = 'request_opportunities';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tbl)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            if (!Schema::hasColumn($this->tbl, 'duedate')) {
                $table->date('duedate')->nullable()->after('to_date');
            }
            if (!Schema::hasColumn($this->tbl, 'duration')) {
                $table->string('duration')->nullable()->after('duedate');
            }
            if (!Schema::hasColumn($this->tbl, 'country_id')) {
                $table->unsignedInteger('country_id')->nullable()->after('location');
            }
            if (Schema::hasColumn($this->tbl, 'customer_id')) {
                $table->dropForeign('request_opportunities_customer_id_foreign');
                $table->dropColumn('customer_id');
            }
            if (!Schema::hasColumn($this->tbl, 'number_recieved')) {
                $table->unsignedInteger('number_recieved')->nullable()->after('number_member');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

}
