<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\ManageTime\Model\LeaveDay;

class CreateTableLeaveDayBaseline extends Migration
{
    protected $tbl = 'leave_day_baseline';

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
            $table->string('month', 8);
            $table->unsignedInteger('employee_id');
            $table->decimal('day_last_year', 8, 2);
            $table->decimal('day_last_transfer', 8, 2);
            $table->decimal('day_current_year', 8, 2);
            $table->decimal('day_seniority', 8, 2);
            $table->decimal('day_ot', 8, 2);
            $table->decimal('day_used', 8, 2);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->foreign('employee_id')
                    ->references('id')
                    ->on('employees')
                    ->onDelete('cascade');
            $table->unique(['employee_id', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tbl);
    }
}
