<?php

namespace Rikkei\Test\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Resource\Model\Candidate;
use Rikkei\SlideShow\Model\Repeat;
use Validator;

class CandidateController extends Controller {
    
    /**
     * redirect to input form information
     * @return type
     */
    public function inputInfor(Request $request) {
        $item = null;
        if ($request->has('id') && ($id = $request->get('id'))) {
            $item = Candidate::where('email', $id)->first();
            if (!$item) {
                abort(404);
            }
        }
        return view('test::candidate.input_infor', compact('item'));
    }
    
    /**
     * save informatioin
     * @param Request $request
     * @return type
     */
    public function saveInfor(Request $request) {
        $valid = Validator::make($request->except(['_token', 'id']), [
            'fullname' => 'required|max:255',
            'birthday' => 'required',
            'email' => 'required|email|max:255',
            'identify' => 'required|max:255',
            'issued_date' => 'required',
            'issued_place' => 'required|max:255',
            'home_town' => 'required|max:255',
            'mobile' => 'required|max:11',
            'position_apply_input' => 'required',
            'offer_salary_input' => 'required',
            'offer_start_date' => 'required|max:255',
            'had_worked' => 'required|max:255',
            'channel_input' => 'required'
        ], [
            '*.required' => trans('test::validate.this_field_is_required'),
            'phone_number.max' => trans('test::validate.this_field_max_character', ['max' => 11]),
            '*.max' => trans('test::validate.this_field_max_character', ['max' => 255])
        ]);
        if ($valid->fails()) {
            return redirect()->back()->withErrors($valid->errors())->withInput();
        }
        $email = $request->get('email');
        $item = Candidate::where('email', $email)->first();
        if (!$item) {
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('test::validate.email_not_found')]]);
        }
        $fillable = $item->getFillable();
        $data = array_only($request->all(), $fillable);
        $item->update($data);
        $item->save();
        
        return redirect()->route('test::candidate.view_infor', ['id' => $item->email])->with('messages', ['success' => [trans('test::validate.save_successful')]]);
    }
     
    /**
     * view item after save
     * @param type $id
     * @return type
     */
    public function view(Request $request) {
        if (!$request->has('id') || !$request->get('id')) {
            abort(404);
        }
        $id = $request->get('id');
        $item = Candidate::where('email', $id)->first();
        if (!$item) {
            abort(404);
        }
        return view('test::candidate.view', compact('item'));
    }
    
    public function checkCandidateInfo(Request $request)
    {
        $id = $request->id;
        try {
            $candidate = Candidate::find($id, ['id', 'fullname', 'email', 'offer_salary_input', 'offer_start_date', 'position_apply_input', 'updated_at']);
            if (!$candidate) {
                return response()->json([
                    'status' => 0,
                    'message' => trans('test::validate.candidate_not_found'),
                ]);
            }
            $now = Carbon::now();
            if (!$candidate->fullname || !$candidate->offer_salary_input) {
                return response()->json([
                    'status' => 1,
                    'text' => trans('test::test.Confirm input_candidate_info'),
                    'url' => route('test::candidate.input_infor')
                ]);
            } else {
                if (!$candidate->offer_start_date ||
                    ($candidate->offer_start_date && $candidate->offer_start_date < $now->format('Y-m-d'))) {
                    return response()->json([
                        'status' => 1,
                        'text' => trans('test::test.Confirm update candidate info'),
                        'url' => route('test::candidate.input_infor', ['id' => $candidate->email]),
                    ]);
                }
            }
            return response()->json([
                'status' => 1,
                'text' => '',
                'url' => '',
            ]);
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
            return response()->json([
                'status' => 0,
                'message' => trans('test::validate.candidate_not_found'),
            ]);
        }
    }
}
