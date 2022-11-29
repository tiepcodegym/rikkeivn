<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableProjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('projects')) {
            return;
        }
        Schema::create('projects', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cust_company_id');
            $table->unsignedInteger('cust_contact_id');
            $table->unsignedInteger('manager_id')->nullable();
            $table->string('name', 255);
            $table->text('description');
            $table->smallInteger('state')->nullable();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->unsignedSmallInteger('type');
            $table->decimal('size', 10, 0)->nullable();
            $table->decimal('price', 10, 0)->nullable();
            $table->decimal('incurred_cost', 10, 0)->nullable();
            $table->decimal('total_cost', 10, 0)->nullable();
            $table->string('documents_url', 255)->nullable();
            $table->string('source_url', 255)->nullable();
            $table->string('issues_url', 255)->nullable();
            $table->string('homepage_url', 255)->nullable();
            $table->dateTime('created_at');
            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            
            $table->index('cust_contact_id');
            $table->index('cust_company_id');
            $table->index('manager_id');
            $table->foreign('cust_company_id')
                ->references('id')
                ->on('cust_companies');
            $table->foreign('cust_contact_id')
                ->references('id')
                ->on('cust_contacts');
            $table->foreign('manager_id')
                ->references('id')
                ->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('projects');
    }
}
