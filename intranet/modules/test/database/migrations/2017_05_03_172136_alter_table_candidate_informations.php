<?php
 
 use Illuminate\Database\Schema\Blueprint;
 use Illuminate\Database\Migrations\Migration;
 
 class AlterTableCandidateInformations extends Migration
 {
     
     protected $tbl = 'candidate_informations';
     
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
             if (!Schema::hasColumn($this->tbl, 'email')) {
                 $table->string('email')->nullable()->after('full_name');
             }
             if (!Schema::hasColumn($this->tbl, 'recruiter')) {
                 $table->string('recruiter')->nullable()->after('relatives');
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
         if (!Schema::hasTable($this->tbl)) {
             return;
         }
         Schema::table($this->tbl, function (Blueprint $table) {
             if (Schema::hasColumn($this->tbl, 'email')) {
                 $table->dropColumn('email');
             }
             if (Schema::hasColumn($this->tbl, 'recruiter')) {
                 $table->dropColumn('recruiter');
             }
         });
     }
	
 }