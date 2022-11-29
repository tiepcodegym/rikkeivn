<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEmployeesOffcialDate extends Migration
{
    private $table = 'employees';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table)) {
            return false;
        }
        Schema::table($this->table, function (Blueprint $table) {
            if (Schema::hasColumn($this->table, 'offcial_date')) {
                $table->date('offcial_date')
                    ->nullable()
                    ->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {}
}
