<?php

namespace Rikkei\Tag\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Tag\Model\Field;
use Rikkei\Tag\View\TagConst;
use Rikkei\Tag\Model\TagValue;
use Illuminate\Support\Facades\Input;
use Rikkei\Tag\Model\Tag;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\TeamConst;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\View as CoreView;
use Illuminate\Support\Facades\Lang;
use Rikkei\Tag\Model\ViewProjTag;
use Rikkei\Tag\Model\ProjectTag;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Core\View\Menu;
use Rikkei\Tag\Model\TagEmployee;
use Carbon\Carbon;

class SearchProjectController extends Controller 
{
    /**
     * index page search project
     */
    public function index()
    {
        if (!Permission::getInstance()->isAllow(TagConst::RA_VIEW_SEARCH)) {
            CoreView::viewErrorPermission();
        }
        Menu::setActive('project', 'tag/field/manage');
        Breadcrumb::add(Lang::get('tag::view.Search tag project'));
        return view('tag::search.project.index', [
            'projectTypes' => ProjectTag::labelTypeProject()
        ]);
    }
    
    /**
     * get data noral init page search
     * 
     *      field path
     */
    public function getDataNormal()
    {
        $response = [];
        if (!Permission::getInstance()->isAllow(TagConst::RA_VIEW_SEARCH)) {
            $response['message'] = Lang::get('core::message.You don\'t have access');
            return response()->json($response, 401);
        }
        $response['fieldsPath'] = Field::getFieldPath(
            TagConst::SET_TAG_PROJECT,
            [TagConst::FIELD_TYPE_TAG]
        );
        $response['team'] = Team::getTeamPathTree();
        $response['maxMM'] = ViewProjTag::getMaxBillable();
        return response()->json($response);
    }
    
    /**
     * get most tag popular
     * 
     * @return json string
     */
    public function getMostTag()
    {
        $response = [];
        $response['tagsOfField'] = [];
        $response['projectsList'] = [];
        // get tag selectd
        // input format: field[fieldId]=tagId1-tagId2&field[fieldId2]=tagId3
        $filterParams = Input::get();
        $fields = [];
        if (isset($filterParams['field'])) {
            $fields = json_decode($filterParams['field'], true);
        }
        unset($filterParams['field']);
        $projectFull = Input::get('proj_filter');
        if ($projectFull) {
            $projectFull = explode('-', $projectFull);
        }
        $dataParams = [
            'field' => [],
            'tagIds' => [],
            'fieldIds' => [],
            'tagsName' => []
        ];
        if (isset($filterParams['tag'])) {
            $dataParams['tagsName'] = explode('::', $filterParams['tag']);
        }
        unset($filterParams['tag']);
        unset($filterParams['proj_filter']);
        if (count($fields)) {
            foreach ($fields as $fieldId => $stringTagId) {
                $tagIds = (array) array_filter(explode('-', $stringTagId), 
                    function($item) {
                        if (is_numeric($item)) {
                            return true;
                        }
                    });
                if (count($tagIds)) {
                    $dataParams['field'][$fieldId] = $tagIds;
                    $dataParams['tagIds'] = array_merge($tagIds, $dataParams['tagIds']);
                    $dataParams['fieldIds'][] = $fieldId;
                }
            }
        }
        $option = [
            'filter' => $filterParams,
            'projectFull' => $projectFull
        ];
        $response['tagsOfField'] = TagValue::getMostTagsOfField(
            $dataParams,
            $option,
            TagConst::MAX_TAG_OF_FIELD
        );
        if (isset($option['resultProjects'])) {
            $response['projectsList'] = $option['resultProjects'];
        }
        if (isset($option['resultNumberTagOfField'])) {
            $response['numberTagOfField'] = $option['resultNumberTagOfField'];
        }
        if (isset($option['resultTotalProjInField'])) {
            $response['totalProjInField'] = $option['resultTotalProjInField'];
        }
        
        if (isset($option['resultTeamCountProj'])) {
            $response['teamCountProj'] = $option['resultTeamCountProj'];
        }
        return response()->json($response);
    }
    
    /**
     * search tag more
     */
    public function getTagMore()
    {
        if (!Permission::getInstance()->isAllow(TagConst::RA_VIEW_SEARCH)) {
            $response['message'] = Lang::get('core::message.You don\'t have access');
            return response()->json($response, 401);
        }
        $fieldId = Input::get('fieldId');
        $response = [];
        if (!$fieldId) {
            return response()->json($response);
        }
        $response = Tag::getMoreTagOfField(
            $fieldId, 
            Input::get('q'),
            Input::get('tagIdsExists')
        );
        return response()->json($response);
    }

    /**
     * get employee list references project
     * @return type
     */
    public function getEmployeesList() 
    {
        if (!Permission::getInstance()->isAllow(TagConst::RA_VIEW_SEARCH)) {
            return CoreView::viewErrorPermission();
        }
        $projectIdStr = Input::get('project_ids');
        $projectIds = [];
        if ($projectIdStr) {
            $projectIds = explode('-', $projectIdStr);
        }
        return TagValue::getEmployeesList($projectIds, Input::all());
    }
    
    /**
     * get search tag of all field
     */
    public function getSearchTag()
    {
        $search = Input::get('q');
        $exists = (array) Input::get('exists');
        if (!$search) {
            return null;
        }
        return response()->json(Tag::geSearchTag($search, $exists));
    }
    
    /**
     * get teams of leaders
     */
    public function getLeaderTeam()
    {
        $leaderIds = Input::get('leader');
        if (!$leaderIds) {
            return response()->json(['leader' => []]);
        }
        return response()->json([
            'leader' => TeamMember::getTeamOfEmployee(explode('-', $leaderIds))
        ]);
    }
    
    /**
     * get employee busy rate data
     */
    public function getEmployeeBusyRate()
    {
        $employeeIds = Input::get('employee');
        if (!$employeeIds) {
            return null;
        }
        $employeeIds = preg_split('/-/', $employeeIds);
        if (!count($employeeIds)) {
            return null;
        }
        $now = Carbon::now();
        return response()->json([
            'month' => $now->format('Y-m-') . '01 00:00:00',
            'employee' => TagEmployee::getBusyRate($employeeIds, $now)
        ]);
    }
}
