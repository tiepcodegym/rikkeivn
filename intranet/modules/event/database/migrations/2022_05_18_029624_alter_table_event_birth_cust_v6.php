<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableEventBirthCustV6 extends Migration
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
        if (!Schema::hasColumn('event_birth_cust', 'golf')) {
            Schema::table('event_birth_cust', function (Blueprint $table) {
                $table->tinyinteger('golf')->default(0)->comment('Tham gia thi gôn: 0- Không tham gia; 1 - Có tham gia');
            });
        }
        if (!Schema::hasColumn('event_birth_cust', 'du_thuyen')) {
            Schema::table('event_birth_cust', function (Blueprint $table) {
                $table->tinyinteger('du_thuyen')->default(0)->comment('Tham gia du thuyền: 0- Không tham gia; 1 - Có tham gia');
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
