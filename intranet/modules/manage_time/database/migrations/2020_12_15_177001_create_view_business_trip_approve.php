<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class CreateViewBusinessTripApprove extends Migration
{
    protected $view = 'view_business_trip_approve';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->view)) {
            DB::statement("drop view {$this->view}");
        }

        DB::statement("CREATE view {$this->view} AS SELECT
                    e.employee_id,
                    emp.employee_code,
                    emp.name,
                    emp.email,
                    e.register_id,
                    e.start_at,
                    e.end_at,
                    e.team_id,
                    b.id,
                    b.creator_id,
                    b.approver_id,
                    b.location,
                    b.company_customer,
                    b.purpose,
                    b.created_at,
                    b.updated_at,
                    b.country_id,
                    b.province_id,
                    b.is_long
            FROM  business_trip_employees e
            inner join business_trip_registers b on e.register_id = b.id
            inner join employees as emp on emp.id = e.employee_id
            where b.deleted_at IS NULL
                AND b.status = 4 
                AND b.parent_id IS NULL
            group by e.employee_id, e.register_id
            order by e.employee_id, e.start_at
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->view);
    }
}
