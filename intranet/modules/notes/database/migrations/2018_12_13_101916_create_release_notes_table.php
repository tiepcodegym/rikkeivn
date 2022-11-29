<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReleaseNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if (Schema::hasTable('release_notes')) {
            return false;
        }
        Schema::create('release_notes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('version', 50)->nullable();
            $table->boolean('status')->default(1);
            $table->text('content')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('release_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('release_notes');
    }
}
