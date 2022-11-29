<?php

namespace Rikkei\Test\Http\Controllers\Admin;

use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\Test\Models\Candidate;
use Rikkei\Resource\Model\Candidate as RsCandidate;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Resource\View\getOptions;
use Validator;
use Carbon\Carbon;
use DB;

class CandidateController extends Controller
{
    /**
     * construct
     */
    public function _construct() {
        Breadcrumb::add(trans('test::test.candidate_infor'), route('test::admin.type.index'));
        Menu::setActive('hr');
    }
    
    /**
     * list types
     * @return type
     */
    public function index() {
        $collectionModel = Candidate::getGridData();
        return view('test::manage.candidate.index', compact('collectionModel', 'fields'));
    }
    
    /**
     * view item
     * @param type $id
     */
    public function show($id) {
        $item = Candidate::find($id);
        if (!$item) {
            abort(404);
        }
        $fields = Candidate::getFields();
        return view('test::manage.candidate.show', compact('item', 'fields'));
    }
    
    /**
     * view edit item
     * @param type $id
     * @return type
     */
    public function edit($id) {
        $item = Candidate::find($id);
        if (!$item) {
            abort(404);
        }
        $fields = Candidate::getFields();
        $edit = true;
        return view('test::manage.candidate.show', compact('item', 'fields', 'edit'));
    }
    
    /**
     * update item
     * @param type $id
     * @param Request $request
     * @return type
     */
    public function update($id, Request $request) {
        $item = Candidate::find($id);
        if (!$item) {
            abort(404);
        }
        $valid = Validator::make($request->except(['_token', 'id']), [
            'full_name' => 'required|max:255',
            'birth' => 'required',
            'email' => 'required|email|max:255|unique:candidate_informations,email,' . $id,
            'identify' => 'required|max:255',
            'home_town' => 'required|max:255',
            'phone_number' => 'required|numeric|digits_between:1,11',
            'position' => 'required',
            'salary' => 'required',
            'start_time' => 'required|max:255',
            'had_worked' => 'required|max:255',
            'hear_recruitment' => 'required'
        ], [
            '*.required' => trans('test::validate.this_field_is_required'),
            'phone_number.max' => trans('test::validate.this_field_max_character', ['max' => 11]),
            '*.max' => trans('test::validate.this_field_max_character', ['max' => 255])
        ]);
        if ($valid->fails()) {
            return redirect()->back()->withInput()->withErrors($valid->errors());
        }
        $fillable = $item->getFillable();
        $data = array_only($request->all(), $fillable);
        foreach ($data as $key => $value) {
            $item->{$key} = $value;
        }
        $item->save();
        return redirect()->route('test::candidate.admin.index');
    }
    
    /**
     * import all candidate information to candidate list
     * @return type
     */
    public function import() {
        $testCandidateTbl = Candidate::getTableName();
        
        // get all data
        $data = DB::table($testCandidateTbl)->get();
        if (!$data) {
            return redirect()->back()->with('messages', ['errors' => [trans('test::view.no_item')]]);
        }
        
        DB::beginTransaction();
        try {
            foreach ($data as $item) {
                if (!$item->email) {
                    continue;
                }
                $candidate = RsCandidate::where('email', $item->email)->first();
                if (!$candidate) {
                    $candidate = new RsCandidate();
                    
                    $candidate->email = $item->email;
                    $candidate->status = getOptions::CONTACTING;
                }
                $birthDay = Carbon::createFromFormat('d/m/Y', $item->birth);
                $candidate->mobile = $item->phone_number;
                $candidate->birthday = $birthDay->format('Y-m-d');
                $candidate->fullname = $item->full_name;
                
                $candidate->offer_salary_input = $item->salary;
                $candidate->offer_start_date = $item->start_time;
                $candidate->position_apply_input = $item->position;
                $candidate->home_town = $item->home_town;
                $candidate->identify = $item->identify;
                $candidate->had_worked = $item->had_worked;
                $candidate->channel_input = $item->hear_recruitment;
                $candidate->relative_worked = $item->relatives;
                $candidate->recruiter = $item->recruiter;

                $candidate->save();
            }
            DB::commit();
            return redirect()->back()->with('messages', ['success' => [trans('test::validate.action_success')]]);
        } catch (\Exception $ex) {
            DB::rollback();
            return redirect()->back()->with('messages', ['errors' => [trans('test::validate.na_error')]]);
        }
    }
    
    /**
     * delete type
     * @param type $id
     * @return type
     */
    public function destroy($id) {
        $item = Candidate::find($id);
        if (!$item) {
            abort(404);
        }
        $item->delete();
        return redirect()->back()->with('messages', ['success' => [trans('test::test.delete_successful')]]);
    }
}
