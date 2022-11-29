<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableEventBirthCustV5 extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('event_birth_cust')) {
            return false;
        }
        if (!Schema::hasColumn('event_birth_cust', 'customer_type')) {
            Schema::table('event_birth_cust', function (Blueprint $table) {
                $table->tinyinteger('customer_type')->default(0)->comment('Loại khách hàng: 0- Khách hàng gửi mail qua tool; 1 - Khách hàng được mời trực tiếp');
            });
        }
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
