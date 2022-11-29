<?php

namespace Rikkei\CallApi\Http\Controllers\Sonar;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\CallApi\Helpers\Sonar;
use Rikkei\Project\Model\Project;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\CallApi\Helpers\CallApiConst;
use Rikkei\Project\Model\SourceServer;
use Rikkei\CallApi\Helpers\Jenkins;
use Exception;

class ProjectController extends Controller
{
    public function create($id, $type = null)
    {
        $project = Project::find($id);
        if (!$project) {
            $response['success'] = 0;
            $response['message'] = trans('call_api::message.Not found item');
            return $response;
        }
        $permission = ViewProject::checkPermissionEditWorkorder($project);
        if (!$permission['persissionEditPM'] && !$permission['permissionEditSubPM']) {
            $response['success'] = 0;
            $response['message'] = trans('call_api::message.You dont have access');
            return $response;
        }
        $sourceServer = SourceServer::getSourceServer($id);
        if ($type === 'jenkins') {
            Jenkins::setValue($project, $sourceServer);
            try {
                return Jenkins::create();
            } catch (Exception $ex) {
                $response['error'] = 1;
                $response['message'] = $ex->getMessage();
                return $response;
            }
        }
        if (!$sourceServer->id_sonar || !$sourceServer->id_jenkins) {
            $response['success'] = 0;
            $response['message'] = trans('call_api::message.Not found project key of sonar or jenkins');
            return $response;
        }
        // search project
        try {
            $isProject = Sonar::isProjectExist($sourceServer->id_sonar);
        } catch (Exception $ex) {
            $response['error'] = 1;
            $response['message'] = $ex->getMessage();
            return $response;
        }
        if (is_array($isProject)) {
            return $isProject;
        }
        if ($isProject) {
            $response['success'] = 1;
            $response['message'] = trans(
                'call_api::message.Project :proj exists',
                ['proj' => $project->name]
            );
            return $response;
        }
        $api = Sonar::connect()->post('projects/create', ['form_params' => [
            'name' => $project->name,
            'project' => $sourceServer->id_sonar,
        ]]);
        $response = [];
        if ($api->getStatusCode() != 200) {
            $response['error'] = 1;
            $response['message'] = Sonar::getError($api);
            return $response;
        }
        $this->addMemberToProjSonar($project, $sourceServer->id_sonar);
        $response['success'] = 1;
        $response['message'] = trans('call_api::message.Create project success');
        return $response;
    }

    /**
     * add member to project
     *
     * @param object $project
     * @param string $projectKey
     * @return boolean
     */
    protected function addMemberToProjSonar($project, $projectKey)
    {
        $members = ProjectMember::getMemberAprroved($project->id);
        if (!count($members)) {
            return true;
        }
        $allPermission = CallApiConst::SONAR_PERMISSIONS_PROJ;
        $permissDev = CallApiConst::sonarPermisDev();
        foreach ($members as $member) {
            $account = preg_replace('/@.*/', '', $member->email);
            if (Sonar::isUserExists($account) !== true) {
                continue;
            }
            if ($member->type == ProjectMember::TYPE_PM || $member->type == ProjectMember::TYPE_SUBPM) {
                $this->addPermissionToUser($account, $allPermission, $projectKey);
            } else {
                $this->addPermissionToUser($account, $permissDev, $projectKey);
            }
        }
        return true;
    }

    /**
     * add permission project to user
     *
     * @param string $account
     * @param array $permission
     * @param string $projectKey
     * @return type
     */
    protected function addPermissionToUser($account, array $permission, $projectKey = null)
    {
        foreach ($permission as $per) {
            Sonar::connect()->post('permissions/add_user', ['form_params' => [
                'login' => $account,
                'permission' => $per,
                'projectKey' => $projectKey
            ]]);
        }
    }
}
