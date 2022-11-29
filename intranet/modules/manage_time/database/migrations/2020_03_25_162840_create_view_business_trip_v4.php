<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateViewBusinessTripV4 extends Migration
{

    protected $view = 'view_business_trip';

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

        DB::statement("create view {$this->view} as  SELECT 
                            employee_team_history.team_id,
                            employee_team_history.employee_id,
                            employees.name AS employee_name,
                            employees.employee_code,
                            employees.email,
                            employees.leave_date as leave_date,
                            business_trip_employees.register_id AS register_id,
                            business_trip_employees.start_at AS start_at,
                            business_trip_employees.end_at AS end_at,
                            GROUP_CONCAT(DATE(employee_team_history.start_at) SEPARATOR ';') AS team_start_at,
                            GROUP_CONCAT(IFNULL(DATE(employee_team_history.end_at), 'NULL') SEPARATOR ';') AS team_end_at,
                            teams.parent_id AS team_parent_id,
                            business_trip_registers.country_id,
                            teams.name AS team_name,
                            business_trip_registers.number_days_business_trip,
                            business_trip_registers.purpose,
                            business_trip_registers.location AS location,
                            business_trip_employees.note AS note
                          FROM
                            employee_team_history 
                            RIGHT JOIN business_trip_employees 
                              ON employee_team_history.employee_id = business_trip_employees.employee_id 
                              AND (
                                employee_team_history.start_at IS NULL 
                                OR employee_team_history.start_at <= business_trip_employees.end_at
                              ) 
                              AND (
                                employee_team_history.end_at IS NULL 
                                OR employee_team_history.end_at >= business_trip_employees.start_at
                              ) 
                              AND employee_team_history.deleted_at IS NULL 
                            JOIN employees 
                              ON employees.id = employee_team_history.employee_id 
                              AND employees.deleted_at IS NULL
                            JOIN teams 
                              ON teams.id = employee_team_history.team_id 
                              AND teams.deleted_at IS NULL 
                            JOIN business_trip_registers 
                              ON business_trip_registers.id = business_trip_employees.register_id 
                              AND business_trip_registers.deleted_at IS NULL 
                              AND business_trip_registers.status = 4 
                          WHERE employee_team_history.id IS NOT NULL 
                          GROUP BY employee_team_history.team_id,
                            employee_team_history.employee_id ,
                            business_trip_registers.id
                          ORDER BY teams.id,
                            business_trip_employees.end_at ");
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
