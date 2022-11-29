<?php

namespace Rikkei\Project\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Project\Model\Project;
use Rikkei\Team\View\Permission;
use Illuminate\Support\Facades\Input;
use Rikkei\Project\Model\ProjectMember;
use Illuminate\Support\Facades\Lang;
use Rikkei\Project\View\ProjectRedmine;
use Rikkei\Project\Model\ProjectPoint;
use Rikkei\Project\View\ProjectGitlab;
use Rikkei\Project\Model\SourceServer;
use Illuminate\Support\Facades\Session;
use Log;

class SoruceServerController extends Controller
{
    /**
     * sync source server
     */
    public function sync($id)
    {
        try {
            $project = Project::find($id);
            $type = Input::get('type');
            $response = [];
            if (!$project || !in_array($type, ['redmine', 'git'])) {
                $response['error'] = 1;
                $response['message'] = Lang::get('project::message.Not found item.');
                return response()->json($response);
            }
            $projectSourceInfo = SourceServer::getSourceServer($id);
            $isSync = SourceServer::getSyncSourceServer($id, $projectSourceInfo);
            if (!$isSync[$type]) {
                $response['error'] = 1;
                $response['message'] = Lang::get('project::message.Not found item.');
                return response()->json($response);
            }
            if (!$project->isOpen()) {
                $response['error'] = 1;
                $response['message'] = Lang::get('project::message.Project closed');
                return response()->json($response);
            }
            //check permission edit
            if (Permission::getInstance()->isScopeCompany(null, 'project::project.edit')) {
            } elseif (Permission::getInstance()->isScopeTeam(null, 'project::project.edit')) {
                $teamsProject = $project->getTeamIds();
                $teamsEmployee = Permission::getInstance()->getTeams();
                $intersect = array_intersect($teamsEmployee, $teamsProject);
                if (count($intersect)) {
                } else {
                    $response['error'] = 1;
                    $response['message'] = Lang::get('project::message.Your parts do not match those approved on the Basic tab');
                    return response()->json($response);
                }
            } else { //edit self project 
                $members = $project->getMemberTypes();
                $employeeCurrent = Permission::getInstance()->getEmployee();
                if (isset($members[ProjectMember::TYPE_PM]) && 
                    in_array($employeeCurrent->id, $members[ProjectMember::TYPE_PM])
                ) {
                } elseif ( 
                    (isset($members[ProjectMember::TYPE_PQA]) && 
                    in_array($employeeCurrent->id, $members[ProjectMember::TYPE_PQA])) ||
                    (isset($members[ProjectMember::TYPE_SQA]) && 
                    in_array($employeeCurrent->id, $members[ProjectMember::TYPE_SQA]))
                ) {
                } elseif (isset($members[ProjectMember::TYPE_SUBPM]) && 
                    in_array($employeeCurrent->id, $members[ProjectMember::TYPE_SUBPM])
                ) {
                } else {
                    $response['error'] = 1;
                    $response['message'] = Lang::get('project::message.Only PM, sub-PM, PQA, SQA can create');
                    return response()->json($response);
                }
            }
            switch ($type) {
                case 'redmine':
                    $result = $this->syncRedmine($project, $projectSourceInfo);
                    if ($result) {
                        $message = Lang::get('project::message.Sync redmine success!');
                        $response['success'] = 1;
                        $response['message'] = $message;
                        if (Input::get('reload')) {
                            Session::flash(
                                'messages', [
                                        'success'=> [
                                            $message,
                                        ]
                                    ]
                            );
                            $response['popup'] = 1;
                            $response['reload'] = 1;
                        }
                    } else {
                        $response['error'] = 1;
                        $response['message'] = Lang::get('project::message.Redmine account dont have access, sync error');
                    }
                    break;
                case 'git':
                    if (!$this->syncGit($project, $projectSourceInfo)) {
                        $response['error'] = 1;
                        $response['message'] = Lang::get('project::message.Not found project in gitlab');
                        break;
                    }
                    $response['success'] = 1;
                    $response['message'] = Lang::get('project::message.Project created gitlab');
                    break;
                default:
                    $response['success'] = 1;
                    $response['message'] = Lang::get('project::message.Sync success!');
                    break;
            }
            return response()->json($response);
        } catch (Exception $ex) {
            Log::info($ex);
        }
        
    }
    
    /**
     * sync redmine
     *  create project + member + count bug
     * 
     * @param type $project
     * @param type $projectMeta
     * @return boolean
     */
    protected function syncRedmine($project, $projectMeta)
    {
        $redmince = ProjectRedmine::getInstance();
        $addProject = $redmince->addProject($projectMeta->id_redmine, $project->name);
        if (!$addProject) {
            return false;
        }
        // $redmince->addMemberToSystem($projectMeta->id_redmine, $project);
        $redmince->addMemberToRedmine($projectMeta->id_redmine, $project);
        $bug = $redmince->countBug($projectMeta);
        $activity = $redmince->countActivity($projectMeta);
        if (!$bug) {
            return true;
        }
        $projectPoint = ProjectPoint::findFromProject($project->id);
        $projectPoint->qua_leakage_errors = $bug['leakage'];
        $projectPoint->qua_defect_errors = $bug['defect'];
        $projectPoint->qua_defect_reward_errors = $bug['defect_reward'];
        $projectPoint->correction_cost = $activity['correction_cost'];
        $projectPoint->cost_actual_effort = $activity['cost_actual_effort'];
        $projectPoint->save([], ['project' => $project]);
        return true;
    }
    
    /**
     * sync redmine
     *  create project + member + count bug
     * 
     * @param type $project
     * @param type $projectSourceInfo
     * @return boolean
     */
    protected function syncGit($project, $projectSourceInfo)
    {
        $gitlab = ProjectGitlab::getInstance();
        if (!$gitlab->createProject($project, $projectSourceInfo)) {
            return false;
        }
        return true;
    }

    public function listGitLabProjects($page)
    {
        $gitlab = ProjectGitlab::getInstance();
        return view('project::gitlab.projects', [
            'collectionModel' => $gitlab->getAllProjects((int)$page),
        ]);
    }
}