<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRequestOpportunity extends Migration
{
    protected $tbl = 'request_opportunities';

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
            $table->string('name');
            $table->string('code');
            $table->unsignedTinyInteger('priority');
            $table->unsignedTinyInteger('status');
            $table->text('detail')->nullable();
            $table->text('potential')->nullable();
            $table->unsignedTinyInteger('number_member');
            $table->string('lang', 2);
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->string('location')->nullable();
            $table->unsignedInteger('customer_id')->nullable();
            $table->text('note')->nullable();
            $table->unsignedInteger('created_by');
            $table->timestamps();
            $table->foreign('customer_id')
                    ->references('id')
                    ->on('cust_contacts')
                    ->onDelete('set null');
            $table->foreign('created_by')
                    ->references('id')
                    ->on('employees')
                    ->onDelete('cascade');
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
