<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToRequestsTable extends Migration
{
    protected $tbl = 'requests';

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
            if ( !Schema::hasColumn($this->tbl, 'description') ) {
                $table->text('description');
            }
            if ( !Schema::hasColumn($this->tbl, 'benefits') ) {
                $table->text('benefits');
            }
            if ( !Schema::hasColumn($this->tbl, 'job_qualifi') ) {
                $table->text('job_qualifi');
            }
            if ( !Schema::hasColumn($this->tbl, 'location') ) {
                $table->unsignedInteger('location')->nullable();
            } else {
                $table->unsignedInteger('location')->nullable()->change();
            }
            $table->foreign('location')->references('id')->on('work_places')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable($this->tbl)) {
            return;
        }
        Schema::table($this->tbl, function (Blueprint $table) {
            $table->dropIfExists('location');
            $table->dropIfExists('description');
            $table->dropIfExists('benefits');
            $table->dropIfExists('job_qualifi');
        });

    }
}
