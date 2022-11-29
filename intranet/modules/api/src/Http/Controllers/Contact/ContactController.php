<?php

namespace Rikkei\Api\Http\Controllers\Contact;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Rikkei\Api\Helper\Contact;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Resource\View\getOptions;
use Rikkei\Tag\Model\Tag;
use Rikkei\Team\Model\EmplCvAttrValue;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmployeeProjExper;
use Rikkei\Team\Model\EmployeeSkill;
use Rikkei\Team\Model\EmplProjExperTag;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\EmployeeTeamHistory;

/**
 * Description of ContactController
 *
 * @author lamnv
 */
class ContactController extends Controller
{
    /*
     * search employee
     * params: s, per_page, page, orderby, order
     */
    public function searchEmployees(Request $request)
    {
        try {
            $response = Contact::getInstance()->getEmployees($request->all());
            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => Contact::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * get skillsheet by employee email
     * @return array
     */
    public function getSkillsheet()
    {
        $data = $this->getBodyData();
        $roles = array();
        $allRoles = getOptions::getInstance()->getRoles(true);
        $response = [
            'success' => 0,
            'data' => array(),
        ];
        if (!isset($data['email'])) {
            return $response;
        }
        $typesSkill = [
            'language' => [
                'label' => 'program language',
                'ph' => 'pl',
            ],
            'frame' => [
                'label' => 'framework / ide',
                'ph' => 'framework / ide',
            ],
            'database' => [
                'label' => 'db',
                'ph' => 'db',
            ],
            'os' => [
                'label' => 'os',
                'ph' => 'os',
            ],
        ];
        $projPosition = EmployeeProjExper::getResponsiblesDefine();
        try {
            $employee = Employee::getEmployByEmail($data['email']);
            if ($employee) {
                $employeeCvEav = EmplCvAttrValue::getAllValueCV($employee->id);
                $roleSelected = $employeeCvEav->getVal('role');
                $roleSelected = $roleSelected ? json_decode($roleSelected, true) : [];
                if (is_numeric($roleSelected)) $roleSelected = [$roleSelected];
                foreach ($roleSelected as $selected) {
                    $roles[$selected] = $allRoles[$selected];
                }
                $teamIdOfEmp = Team::getListTeamOfEmp($employee->id);
                $eplTeamOfficial = EmployeeTeamHistory::where('employee_id', $employee->id)->where('is_working', Team::WORKING)->first();
                $arrTeam = [];
                if ($eplTeamOfficial) {
                    $teamOfficial = Team::find($eplTeamOfficial->team_id);
                    if ($teamOfficial) {
                        $arrTeam = [
                            $teamOfficial->id => $teamOfficial->name
                        ];
                    }
                }
                $arrayTeamIdOfEmp = [];
                foreach ($teamIdOfEmp as $team) {
                    $arrayTeamIdOfEmp[$team->id] = $team->name;
                }
                $attributeValue = $employeeCvEav->eav;
                $attributeValue['role'] = $roles;
                $attributeValue['teams'] = $arrayTeamIdOfEmp;
                $attributeValue['team_primary'] = $arrTeam;
                $project = EmployeeProjExper::getProjExpersInCv($employee->id)
                    ->map(function ($item) use ($attributeValue) {
                        unset($item->number);
                        if (isset($attributeValue['proj_' . $item->id . '_name_' . $item->lang_code])) {
                            $item->name = $attributeValue['proj_' . $item->id . '_name_' . $item->lang_code];
                        }
                        if (isset($attributeValue['proj_' . $item->id . '_description_' . $item->lang_code])) {
                            $item->description = $attributeValue['proj_' . $item->id . '_description_' . $item->lang_code];
                        }
                        return $item;
                    });

                foreach ($attributeValue as $key => $attribute) {
                    if (strpos($key, 'proj') !== false || strpos($key, 'status') !== false) {
                        unset($attributeValue[$key]);
                    }
                }
                $skillPersonIds = EmployeeSkill::getSkillIdsInCv($employee->id);
                $skillProjIds = EmplProjExperTag::getSkillIdsProjInCv($project);
                $skillsProj = $skillProjIds['data'];
                $tagData = Tag::getTagDataProj();
                $arrPhases = [];
                foreach ($project as $key => $projItem) {
                    $programming_languages = array();
                    $environments = array();
                    $res = array();
                    if (isset($skillsProj[$projItem->id]['lang'])) {
                        foreach ($skillsProj[$projItem->id]['lang'] as $tagItem) {
                            if (isset($tagData['language'][$tagItem['id']])) {
                                $programming_languages[] = $tagData['language'][$tagItem['id']];
                            }
                        }
                    }
                    if (isset($skillsProj[$projItem->id]['other'])) {
                        foreach ($skillsProj[$projItem->id]['other'] as $tagItem) {
                            if (isset($tagData['dev_env'][$tagItem['id']])) {
                                $environments[] = $tagData['dev_env'][$tagItem['id']];
                            }
                        }
                    }
                    if (isset($skillsProj[$projItem->id]['res'])) {
                        foreach ($skillsProj[$projItem->id]['res'] as $tagItem) {
                            if (isset($projPosition[$tagItem['lang']][$tagItem['id']])) {
                                $res[] = $projPosition[$tagItem['lang']][$tagItem['id']];
                                $arrPhases[] = $projPosition[$tagItem['lang']][$tagItem['id']];
                            }
                        }
                    }
                    $projItem->programming_languages = $programming_languages;
                    $projItem->environments = $environments;
                    $projItem->assigned_phases = $res;
                }

                foreach ($typesSkill as $key => $label) {
                    if (isset($skillPersonIds[$key]) && count($skillPersonIds[$key])) {
                        foreach ($skillPersonIds[$key] as $skillData) {
                            if ($key == 'language') {
                                $skillData->language = $tagData['lang'][$skillData->tag_id];
                            }
                            if ($key == 'frame') {
                                $skillData->framework = $tagData['frame'][$skillData->tag_id];
                            }
                            if ($key == 'database') {
                                $skillData->database = $tagData['database'][$skillData->tag_id];
                            }
                            if ($key == 'os') {
                                $skillData->os = $tagData['os'][$skillData->tag_id];
                            }
                        }
                    }
                }
                $skillPersonIds['phases'] = array_unique($arrPhases);
                $response['success'] = 1;
                $response['data']['summary'] = array_merge($employee->toArray(), $attributeValue);
                $response['data']['project'] = $project;
                $response['data']['skill'] = $skillPersonIds;
            }
        } catch (\Exception $ex) {
            Log::info($ex);
            $response['message'] = $ex->getMessage();
        }

        return $response;
    }
}
