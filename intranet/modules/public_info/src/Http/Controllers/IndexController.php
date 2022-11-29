<?php

namespace Rikkei\PublicInfo\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Resource\Model\Candidate;
use Validator;
use Rikkei\Resource\Model\RicodeTest;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function registerCourseBrse()
    {
        return view('public_info::index.regiser_course_brse');
    }

    public function resultRicodeTest(Request $request) {
        $authRicode = $request->header('AuthRicode');
        
        if(!isset($authRicode)) {
            return $this->responseJson('Auth Ricode', 400);  
        }
        if(base64_decode($authRicode) != config('app.auth_ricode')) {
            return $this->responseJson('Auth Ricode', 400);  
        }

        $data = $request->only(
            'total_correct_answers', 
            'title', 
            'start_time', 
            'duration', 
            'exam_id', 
            'candidate_id', 
            'penalty_point',
            'url_view_source'
        );

        $dataValid = Validator::make($data, [
            'total_correct_answers'=> 'required|numeric|min:0',
            'title'=> 'required|min:1|max:255',
            'start_time'=> 'required|date',
            'duration'=> 'required|integer',
            'exam_id'=> 'required|integer',
            'penalty_point'=> 'required|integer',
            'candidate_id' => 'required|integer',
            'url_view_source' => 'string|min:1|max:255',
        ]);

        $candidate = Candidate::find($data['candidate_id']);
        if(!isset($candidate)) {
            return $this->responseJson('Candidate not found', 404);
        }

        if($dataValid->fails()) {
            return $dataValid->errors();
        }
        
        $data['time_remaining'] = $data['duration'];
        unset($data['duration']);

        $ricodeTest = RicodeTest::updateOrCreate(['candidate_id' => $data['candidate_id']], $data);

        if(isset($ricodeTest)) {
            return $this->responseJson('Success', 200); 
        } else {
            return $this->responseJson('error', 400); 
        }
    }
}
