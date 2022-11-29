<?php

namespace Rikkei\Project\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\Resource\Model\Programs;
use Rikkei\Team\Model\Team;
use Rikkei\Project\Model\Opportunity;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Project\View\OpporView;
use Validator;

class OpportunityController extends Controller
{
    public function _construct() {
        Menu::setActive('project');
        Breadcrumb::add(trans('project::view.Opportunity'), route('project::oppor.index'));
    }

    /**
     * list opportunity
     * @return type
     */
    public function index()
    {
        $collectionModel = Opportunity::getOpportunity();
        $opTbl = Opportunity::getTableName();
        return view('project::opportunity.index', compact(
            'collectionModel',
            'opTbl'
        ));
    }

    /**
     * edit opportunity
     * @param type $id
     * @return type
     */
    public function edit($id = null)
    {
        $project = null;
        $quality = null;
        if ($id) {
            $project = Opportunity::findOrFail($id);
        }
        $programsOption = Programs::getListOption();
        $teamPath = Team::getTeamPathTree();
        $allTeamDraft = [];
        $projectPrograms = [];
        $projectSales = collect();
        $customer = null;
        $scopeObject = null;
        if ($project) {
            Breadcrumb::add(trans('project::view.Edit'));

            $allTeamDraft = $project->teamProject()->lists('id')->toArray();
            $projectPrograms = $project->projectLanguages()->lists('id')->toArray();
            $projectSales = $project->saleProject;
            $quality = $project->quality;
            $customer = $project->customerContact;
            $scopeObject = $project->scopeObject;
        } else {
            Breadcrumb::add(trans('project::view.Create'));
        }
        return view('project::opportunity.edit', compact(
            'project',
            'programsOption',
            'teamPath',
            'allTeamDraft',
            'projectPrograms',
            'projectSales',
            'quality',
            'customer',
            'scopeObject'
        ));
    }

    /*
     * check opportunity existis
     */
    public function checkExists(Request $request)
    {
        return Opportunity::checkExists($request->all());
    }

    /**
     * insert or update
     * @param Request $request
     * @return type
     */
    public function store(Request $request)
    {
        $id = $request->get('id');
        $validator = Validator::make($request->all(), [
            'proj.name' => 'required|max:255|unique:'. Opportunity::getTableName() .',name,'.($id ? $id : 'NULL').',id,deleted_at,NULL,status,' . Opportunity::STATUS_OPPORTUNITY,
            'quality.cost_approved_production' => 'numeric|min:0',
            'quality.billable_effort' => 'numeric|min:0',
            'proj.start_at' => 'required|date_format:Y-m-d',
            'proj.end_at' => 'required|date_format:Y-m-d',
            'sale_id' => 'required',
            'team_id' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors($validator->errors());
        }
        if (!OpporView::validateStartAt($request->get('proj')['start_at'], $request->get('proj')['end_at'])) {
            return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['proj.start_at' => trans('The start at must be before end at')]);
        }

        $dataOpportunity = $request->get('proj');
        DB::beginTransaction();
        try {
            $opportunity = Opportunity::insertOrUpdate($dataOpportunity, $id);
            //insert or update quality
            Opportunity::insertOrUpdateQuality($request->get('quality'), $opportunity->id);
            //insert or update team
            $teamIds = $request->get('team_id');
            $opportunity->teamProject()->sync($teamIds ? $teamIds : []);
            //insert or update sale
            $saleIds = $request->get('sale_id');
            $opportunity->saleProject()->sync($saleIds ? $saleIds : []);
            //insert or udpate project languages
            $progLangIds = $request->get('prog_langs');
            $opportunity->projectLanguages()->sync($progLangIds ? $progLangIds : []);
            //update project meta scope object
            $scopeObject = $request->get('scope');
            Opportunity::insertOrUpdateScope($scopeObject, $opportunity->id);

            DB::commit();
            return redirect()
                    ->to(route('project::oppor.edit', $opportunity->id) . $request->get('tab'))
                    ->with('messages', ['success' => [trans('project::message.Save data success!')]]);
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
            return redirect()
                    ->back()
                    ->withInput()
                    ->with('messages', ['errors' => [trans('project::message.Error system')]]);
        }
    }

    /**
     * get tab content
     * @param Request $request
     * @return type
     */
    public function getTabContent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'projectId' => 'required',
            'type' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json('Invalid data!', 422);
        }
        return response()->json(Opportunity::getTabContent($request->all()));
    }

    /**
     * delete opportunity
     * @param type $id
     */
    public function delete($id)
    {
        $item = Opportunity::findOrFail($id);
        $item->delete();
        return redirect()
            ->back()
            ->with('messages', ['success' => [trans('project::message.Delete item success')]]);
    }
}

