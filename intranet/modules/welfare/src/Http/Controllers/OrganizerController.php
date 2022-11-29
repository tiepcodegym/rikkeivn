<?php

namespace Rikkei\Welfare\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use \Illuminate\Http\Request;
use Rikkei\Welfare\Model\Organizer;
use Yajra\Datatables\Facades\Datatables;

class OrganizerController extends Controller
{
    /**
     * get data employee
     */
    public function showDataEmployee() {
        $organizer= Organizer::getWelOrganizer();
        $data = Datatables::of($organizer)
            ->setRowAttr([
                'emp_id' => function($organizer) {
                    return $organizer->emp_id;
                },
            ])
            ->removeColumn('emp_id')
            ->make();
        return $data;
    }

    /**
     * Save data tab organizer
     */
    public function saveData(Request $request) {
        $data = $request->all();
        if (trim($data['name']) == null) {
            $status = false;
        } else {
            $status = Organizer::updateOrganizers($data);
        }
        return response()->json($status);
    }
}