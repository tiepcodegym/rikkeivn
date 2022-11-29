<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rikkei\Document\Models\DocRequest;
use Illuminate\Support\Facades\DB;

class CreateTableDocRequestCreator extends Migration
{
    protected $tbl = 'doc_request_creator';

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
            $table->unsignedInteger('doc_req_id');
            $table->unsignedInteger('creator_id');
            $table->foreign('doc_req_id')
                    ->references('id')
                    ->on('documentrequests')
                    ->onDelete('cascade');
            $table->foreign('creator_id')
                    ->references('id')
                    ->on('employees')
                    ->onDelete('cascade');
            $table->primary(['doc_req_id', 'creator_id']);
        });
        //seed data
        $requests = DocRequest::all();
        if (!$requests->isEmpty()) {
            DB::beginTransaction();
            try {
                $dataInsert = [];
                foreach ($requests as $req) {
                    $item = DB::table($this->tbl)->where('doc_req_id', $req->id)
                            ->where('creator_id', $req->creator_id)
                            ->first();
                    if (!$item) {
                        $dataInsert[] = [
                            'doc_req_id' => $req->id,
                            'creator_id' => $req->creator_id
                        ];
                    }
                }
                if ($dataInsert) {
                    DB::table($this->tbl)->insert($dataInsert);
                }
                DB::commit();
            } catch (\Exception $ex) {
                DB::rollback();
                \Log::info($ex);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
