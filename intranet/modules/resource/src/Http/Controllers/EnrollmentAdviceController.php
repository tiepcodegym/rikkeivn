<?php
namespace Rikkei\Resource\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Http\Controllers\Controller as Controller;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Resource\Model\EnrollmentAdvice;
use Rikkei\Team\View\Config;

class EnrollmentAdviceController extends Controller
{
    /**
     * list data
     * @return type
     */
    public function index()
    {
        Breadcrumb::add('EnrollmentAdvice', route('resource::enroll_addvice.index'));
        $pager = Config::getPagerData(null, ['order' => 'requests.id', 'dir' => 'desc']);
        $enrollments =  EnrollmentAdvice::orderBy('status', 'DESC')->orderBy('created_at', 'DESC');
        if (count($enrollments)) {
            $enrollments = CoreModel::filterGrid($enrollments);
            $enrollments = CoreModel::pagerCollection($enrollments, $pager['limit'], $pager['page']);
        }
        return view('resource::enrollment_advice.list', [
                'collectionModel' => $enrollments,
            ]);
    }
    
    public function updateStatus()
    {
        $enrollmen = EnrollmentAdvice::find($_POST["id"]);
        if ($enrollmen) {
            $enrollmen->status = EnrollmentAdvice::STATE_CLOSE;
            $enrollmen->save();
            return  1;
        }
        return false;
    }

    public function insert(Request $request)
    {
        $return = DB::table('enrollment_advice')->insert(
            [
                'name' => $request->form_data[0]["value"],
                'email' => $request->form_data[1]["value"],
                'phone' => $request->form_data[2]["value"],
                'language' => $request->form_data[3]["value"],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
        if ($return) {
            return response()->json(['data'=>"success", 'messager'=> '', 'code'=> 200]);
        } else {
            return response()->json(['data'=>"error", 'messager'=> '', 'code'=> 204]);
        }
    }
}
