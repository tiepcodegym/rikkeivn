<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEmployeesPassPort extends Migration
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
            if (!Schema::hasColumn($this->table, 'passport_number')) {
                $table->string('passport_number', 50)
                    ->nullable()
                    ->after('id_card_date');
            }
            if (!Schema::hasColumn($this->table, 'passport_date_start')) {
                $table->date('passport_date_start')
                    ->nullable()
                    ->after('passport_number');
            }
            if (!Schema::hasColumn($this->table, 'passport_date_exprie')) {
                $table->date('passport_date_exprie')
                    ->nullable()
                    ->after('passport_date_start');
            }
            if (!Schema::hasColumn($this->table, 'passport_addr')) {
                $table->string('passport_addr')
                    ->nullable()
                    ->after('passport_date_exprie');
            }
            if (Schema::hasColumn($this->table, 'mobile_phone')) {
                $table->string('mobile_phone', 20)
                    ->nullable()
                    ->change();
            }
            if (Schema::hasColumn($this->table, 'home_phone')) {
                $table->string('home_phone', 20)
                    ->nullable()
                    ->change();
            }
            if (Schema::hasColumn($this->table, 'personal_email')) {
                $table->string('personal_email', 100)
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
