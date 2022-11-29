<?php

namespace Rikkei\Api\Helper;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Rikkei\Contract\Model\ContractConfirmExpire;
use Rikkei\Contract\Model\ContractModel;
use Rikkei\Core\Model\User;
use Rikkei\Core\View\CacheBase;
use Rikkei\Core\View\View;
use Rikkei\Resource\View\View as ViewResource;
use Rikkei\Tag\Model\Tag;
use Rikkei\Team\Model\Certificate;
use Rikkei\Team\Model\EmplCvAttrValue;
use Rikkei\Team\Model\Employee as EmployeeModel;
use Rikkei\Team\Model\EmployeeMilitary;
use Rikkei\Team\Model\EmployeeProjExper;
use Rikkei\Team\Model\EmployeeSchool;
use Rikkei\Team\Model\EmployeeSkill;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\Model\EmployeeWork;
use Rikkei\Team\Model\EmplProjExperTag;
use Rikkei\Team\Model\Major;
use Rikkei\Team\Model\MilitaryArm;
use Rikkei\Team\Model\MilitaryPosition;
use Rikkei\Team\Model\MilitaryRank;
use Rikkei\Team\Model\PartyPosition;
use Rikkei\Team\Model\QualityEducation;
use Rikkei\Team\Model\RelationNames;
use Rikkei\Team\Model\School;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\UnionPosition;
use Rikkei\Team\View\EmpLib;
use Rikkei\Resource\Model\Candidate;

/**
 * Description of Contact
 *
 */
class HrmProfile extends HrmBase
{
    /**
     *
     */
    const KEY_CACHE_HRM_INITIAL = 'key_cache_hrm_profile_initial';
    /**
     * @var
     */
    private $ethnologies;
    /**
     * @var
     */
    private $religions;

    /**
     * @var
     */
    private $genderOptions;
    /**
     * @var array
     */
    private $maritalStatus;
    /**
     * @var array|\Rikkei\Core\View\type|null
     */
    private $nationalities;
    /**
     * @var array
     */
    private $contractTypes;
    /**
     * @var \Illuminate\Support\Collection
     */
    private $contractHistories;
    /**
     * @var array
     */
    private $contractStatusConfirmLabel;
    /**
     * @var array
     */
    private $relationships;
    /**
     * @var string
     */
    private $schools;
    /**
     * @var string
     */
    private $schoolMajors;
    /**
     * @var array
     */
    private $schoolLevels;
    /**
     * @var array
     */
    private $schoolRanks;
    /**
     * @var array
     */
    private $cerificateStatus;
    /**
     * @var array
     */
    private $partyOptions;
    /**
     * @var array
     */
    private $unionOptions;
    /**
     * @var array
     */
    private $militaryRanks;
    /**
     * @var array
     */
    private $militaryPositions;
    /**
     * @var array
     */
    private $militaryArmys;
    /**
     * @var array
     */
    private $militaryClassifications;
    /**
     * @var array
     */
    private $skillsheetStatus;
    /**
     * @var array
     */
    private $skillsheetLangLevels;
    /**
     * @var
     */
    private $skillsheetRoles;
    /**
     * @var
     */
    private $projectsSkillLang;
    /**
     * @var array
     */
    private $projectsRolesLang;
    /**
     * @var
     */
    private $projectsProgramingLanguages;
    /**
     * @var
     */
    private $projectsEnviroment;
    /**
     * @var array
     */
    private $projectsPositionsLang;
    /**
     * @var array
     */
    private $teamHistories;
    /**
     * @var \Illuminate\Database\Eloquent\Collection|EmployeeSkill[]
     */
    private $skillEmployees;
    /**
     * @var array
     */
    private $skillTagData;

    /**
     * HrmProfile constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     *  Init data
     */
    public function _initial()
    {
        $availableKeys = HrmProfileCache::getAvailableKey();
        if ($availableKeys) {
            foreach ($availableKeys as $availableKey) {
                if ($initialValue = HrmProfileCache::get($availableKey)) {
                    foreach ($initialValue as $key => $value) {
                        $this->{$key} = $value;
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param array $requestedEmployeeIds
     * @return mixed
     */
    private function _initSkillSheetProjectLang(array $requestedEmployeeIds)
    {
        $emplProjExperTagTbl = EmplProjExperTag::getTableName();
        $emplProjExperTbl = EmployeeProjExper::getTableName();
        $collections = EmployeeProjExper::leftJoin($emplProjExperTagTbl, "${emplProjExperTagTbl}.proj_exper_id", '=', "${emplProjExperTbl}.id")
            ->whereIn('employee_id', $requestedEmployeeIds)->get();
        $collections = $collections->groupBy('employee_id')->transform(function ($item) {
            return $item->groupBy('lang_code')->transform(function ($item) {
                return $item->groupBy('id')->transform(function ($item) {
                    return $item->groupBy('type');
                });
            });
        });

        return $collections;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|EmployeeSkill[]
     */
    private function _initSkillEmployees()
    {
        $employeeSkillsCollections = EmployeeSkill::all();
        $employeeSkillsCollections = $employeeSkillsCollections->groupBy('employee_id')->transform(function ($item) {
            return $item->groupBy('type');
        });

        return $employeeSkillsCollections;
    }

    /**
     * @return array
     */
    public function listApiItems()
    {
        return ['employees', 'achievements', 'welfare', 'identifications', 'contracts', 'skillsheet', 'information'];
    }

    /**
     * @param $value
     * @return int
     */
    private function _convertToInt($value)
    {
        return (int)$value;
    }

    /**
     * @param $object
     * @param $properties
     * @param null $defaultValue
     * @return |null
     */
    private function _getDefaultValue($object, $properties, $defaultValue = null)
    {
        return $object ? $object->{$properties} : $defaultValue;
    }

    /**
     * @param $employee
     * @param $curUser
     * @return array
     */
    private function _getProfileBase($employee)
    {
        $avatar = $employee->aavatar_url;
        $avatar = View::getLinkImage($avatar);
        $avatar = preg_replace('/\?(sz=)(\d+)/i', '', $avatar);
        $ethnology = $this->_convertToInt($employee->folk);
        $religion = $this->_convertToInt($employee->religion);
        $marital = $this->_convertToInt($employee->marital);
        $countryId = $this->_convertToInt($employee->country_id);
        $can = Candidate::where('employee_id', $employee->id)->first(['id', 'employee_id', 'recruiter']);

        return [
            'name' => $employee->name,
            'email' => $employee->email,
            'gender' => [
                'id' => $employee->gender,
                'name' => View::getValueArray($this->genderOptions, [$employee->gender])
            ],
            'birthday' => $employee->birthday,
            'avatar' => $avatar,
            'identify_card' => $employee->id_card_number,
            'identify_card_dated' => View::getDate($employee->id_card_date),
            'identify_card_place' => $employee->id_card_place,
            'identify_passport' => $employee->passport_number,
            'identify_passport_place' => $employee->passport_addr,
            'identify_passport_dated' => View::getDate($employee->passport_date_start),
            'identify_passport_due_date' => View::getDate($employee->passport_date_exprie),
            'ethnology' => [
                'id' => $ethnology,
                'name' => View::getValueArray($this->ethnologies, [$ethnology]),
            ],
            'religion' => [
                'id' => $religion,
                'name' => View::getValueArray($this->religions, [$religion])
            ],
            'marital_status' => [
                'id' => $marital,
                'name' => View::getValueArray($this->maritalStatus, [$marital])
            ],
            'nationality' => [
                'id' => $countryId,
                'name' => View::getValueArray($this->nationalities, [$countryId])
            ],
            'team_histories' => View::getValueArray($this->teamHistories, [$employee->id]),
            'recruiter' => $can ? $can->recruiter : ''
        ];
    }

    /**
     * @param $employeeModelItem
     * @return array
     */
    private function _getProfileWorking($employeeModelItem)
    {
        $employeeRelativeItem = $employeeModelItem->getItemRelate('work');
        $contractType = $this->_convertToInt($employeeRelativeItem ? $employeeRelativeItem->contract_type : null);
        $contractHistory = [];
        if (isset($this->contractHistories[$employeeModelItem->id])) {
            foreach ($this->contractHistories[$employeeModelItem->id] as $contract) {
                $startTime = Carbon::parse($contract->start_at)->format('Y-m-d');
                $endTime = $contract->end_at ? Carbon::parse($contract->end_at)->format('Y-m-d') : '';
                $contractHistory[] = [
                    'id' => $contract->id,
                    'type' => [
                        'id' => $contract->type,
                        'name' => View::getValueArray($this->contractTypes, [$contract->type])
                    ],
                    'delete_at' => $contract->deleted_at,
                    'start_date' => $startTime,
                    'end_date' => $endTime,
                    'confirmation_status' => $contract->confirmExpire ? $this->contractStatusConfirmLabel[$contract->confirmExpire->type] : '',
                    'note' => $contract->confirmExpire ? $contract->confirmExpire->note : ''
                ];
            }
        }

        return [
            'employee_code' => $employeeModelItem->employee_code,
            'id_card' => $employeeModelItem->employee_card_id,
            'email' => $employeeModelItem->email,
            'join_date' =>  View::getDate($employeeModelItem->join_date),
            'probationary_start_date' =>  View::getDate($employeeModelItem->trial_date),
            'probationary_end_date' =>  View::getDate($employeeModelItem->trial_end_date),
            'official_join_date' =>  View::getDate($employeeModelItem->offcial_date),
            'tax_code' => $this->_getDefaultValue($employeeRelativeItem, 'tax_code'),
            'contract_type' => [
                'id' => $contractType,
                'name' => View::getValueArray($this->contractTypes, [$contractType])
            ],
            'contract_histories' => $contractHistory,
            'bank_account_number' => $this->_getDefaultValue($employeeRelativeItem, 'bank_account'),
            'bank_account_name' => $this->_getDefaultValue($employeeRelativeItem, 'bank_name'),
            'social_insurance_book' => $this->_getDefaultValue($employeeRelativeItem, 'insurrance_book'),
            'social_insurance_book_join_date' => $this->_getDefaultValue($employeeRelativeItem, 'insurrance_date'),
            'social_insurance_book_payment_rate' => $this->_getDefaultValue($employeeRelativeItem, 'insurrance_ratio'),
            'health_insurance_number' => $this->_getDefaultValue($employeeRelativeItem, 'insurrance_h_code'),
            'health_insurance_place' => $this->_getDefaultValue($employeeRelativeItem, 'register_examination_place'),
            'health_insurance_due_date' => $this->_getDefaultValue($employeeRelativeItem, 'insurrance_h_expire'),
            'resign_date' =>  View::getDate($employeeModelItem->leave_date),
            'resign_reason' =>  $employeeModelItem->leave_reason,
            'deleted' => $employeeModelItem->deleted_at ? 1 : 0
        ];
    }

    /**
     * @param EmployeeModel $employeeModelItem
     * @return array
     */
    private function _getProfileContact(EmployeeModel $employeeModelItem)
    {
        $employeeContact = $employeeModelItem->getItemRelate('contact');
        $nationalityId = $this->_getDefaultValue($employeeContact, 'native_country');
        $nationalityId2 = $this->_getDefaultValue($employeeContact, 'tempo_country');
        $relationId = $this->_getDefaultValue($employeeContact, 'emergency_relationship');

        return [
            'contact' => [
                'mobile_phone' => $this->_getDefaultValue($employeeContact, 'mobile_phone'),
                'home_phone' => $this->_getDefaultValue($employeeContact, 'home_phone'),
                'office_phone' => $this->_getDefaultValue($employeeContact, 'office_phone'),
                'other_phone' => $this->_getDefaultValue($employeeContact, 'other_phone'),
                'other_email' => $this->_getDefaultValue($employeeContact, 'personal_email'),
                'other_email2' => $this->_getDefaultValue($employeeContact, 'other_email'),
                'yahoo_id' => $this->_getDefaultValue($employeeContact, 'yahoo'),
                'facebook_id' => $this->_getDefaultValue($employeeContact, 'facebook'),
                'skype_id' => $this->_getDefaultValue($employeeContact, 'skype'),
            ],
            'permanent_residence' => [
                'nationality' => [
                    'id' => $nationalityId,
                    'name' => View::getValueArray($this->nationalities, [$nationalityId])
                ],
                'city' => $this->_getDefaultValue($employeeContact, 'native_province'),
                'district' => $this->_getDefaultValue($employeeContact, 'native_district'),
                'ward' => $this->_getDefaultValue($employeeContact, 'native_ward'),
                'address' => $this->_getDefaultValue($employeeContact, 'native_addr'),
            ],
            'temporary_residence' => [
                'nationality' => [
                    'id' => $nationalityId2,
                    'name' => View::getValueArray($this->nationalities, [$nationalityId2]),
                ],
                'city' => $this->_getDefaultValue($employeeContact, 'tempo_province'),
                'district' => $this->_getDefaultValue($employeeContact, 'tempo_district'),
                'ward' => $this->_getDefaultValue($employeeContact, 'tempo_ward'),
                'address' => $this->_getDefaultValue($employeeContact, 'tempo_addr'),
            ],
            'urgent_contact' => [
                'name' => $this->_getDefaultValue($employeeContact, 'emergency_contact_name'),
                'phone' => $this->_getDefaultValue($employeeContact, 'emergency_mobile'),
                'relationship' => [
                    'id' => $relationId,
                    'name' => View::getValueArray($this->relationships, [$relationId]),
                ],
                'address' => $this->_getDefaultValue($employeeContact, 'emergency_addr'),
                'home_phone' => $this->_getDefaultValue($employeeContact, 'emergency_contact_mobile'),
            ]
        ];
    }

    /**
     * @param EmployeeModel $employeeModelItem
     * @return array
     */
    private function _getFamilyInformation(EmployeeModel $employeeModelItem)
    {
        $familyRelationships = $employeeModelItem->familyRelationships;
        $results = [];
        foreach ($familyRelationships as $familyRelationship) {
            $results[] = [
                'id' => $familyRelationship->id,
                'name' => $familyRelationship->name,
                'relationship' => [
                    'id' => $familyRelationship->relationship,
                    'name' => View::getValueArray($this->relationships, [$familyRelationship->relationship])
                ],
                'birthday' => $familyRelationship->date_of_birth,
                'phone' => $familyRelationship->mobile,
                'job' => $familyRelationship->career,
            ];
        }

        return $results;
    }

    /**
     * @param EmployeeModel $employeeModelItem
     * @return array
     */
    private function _getEducationProcess(EmployeeModel $employeeModelItem)
    {
        $educations = $employeeModelItem->educations;
        $results = [];
        foreach ($educations as $education) {
            $results[] = [
                'id' => $education->id,
                'educate_placement' => [
                    'id' => $education->school_id,
                    'name' => View::getValueArray($this->schools, [$education->school_id])
                ],
                'from_date' => $education->start_at,
                'to_date' => $education->end_at,
                'major' => [
                    'id' => $education->major_id,
                    'name' => View::getValueArray($this->schoolMajors, [$education->major_id]),
                ],
                'level' => [
                    'id' => $education->quality,
                    'name' => View::getValueArray($this->schoolLevels, [$education->quality]),
                ],
                'classification' => [
                    'id' => $education->degree,
                    'name' => View::getValueArray($this->schoolRanks, [$education->degree]),
                ],
            ];
        }

        return $results;
    }

    /**
     * @param EmployeeModel $employeeModelItem
     * @return array
     */
    private function _getWorkingProcess(EmployeeModel $employeeModelItem)
    {
        $businesses = $employeeModelItem->businesses;
        $results = [];
        foreach ($businesses as $business) {
            $results[] = [
                'working_placement' => $business->work_place,
                'from_date' => $business->start_at,
                'to_date' => $business->end_at,
                'position' => $business->position
            ];
        }

        return $results;
    }

    /**
     * @param EmployeeModel $employeeModelItem
     * @return array
     */
    private function _getCertificates(EmployeeModel $employeeModelItem)
    {
        $certificates = $employeeModelItem->employeeCerties;
        $results = [];
        foreach ($certificates as $certificate) {
            $files = [];
            if (!empty($certificate->certImages)) {
                foreach ($certificate->certImages as $file) {
                    $files[] = $file->image;
                }
            }

            $results[] = [
                'id' => $certificate->id,
                'type' => $certificate->name,
                'level' => $certificate->level,
                'valid_from' => $certificate->start_at,
                'valid_to' => $certificate->end_at,
                'files' => $files,
                'status' => [
                    'id' => $certificate->status,
                    'name' => View::getValueArray($this->cerificateStatus, [$certificate->status]),
                ],
            ];
        }

        return $results;
    }

    /**
     * @param EmployeeModel $employeeModelItem
     * @return array
     */
    private function _getScanExhibits(EmployeeModel $employeeModelItem)
    {
        $attaches = $employeeModelItem->scanExhibits;
        $results = [];
        foreach ($attaches as $attach) {
            $results[] = [
                'title' => $attach->title,
                'note' => $attach->note,
                'files' => $attach->attachFiles,
            ];
        }

        return $results;
    }

    /**
     * @param EmployeeModel $employeeModelItem
     * @return array
     */
    private function _getHabits(EmployeeModel $employeeModelItem)
    {
        $employeeHobby = $employeeModelItem->getItemRelate('hobby');

        return [
            'hobby' => $this->_getDefaultValue($employeeHobby, 'hobby_content'),
            'personal_goal' => $this->_getDefaultValue($employeeHobby, 'personal_goal'),
            'gifted' => $this->_getDefaultValue($employeeHobby, 'hobby'),
            'strengths' => $this->_getDefaultValue($employeeHobby, 'forte'),
            'weaknesses' => $this->_getDefaultValue($employeeHobby, 'weakness'),
        ];
    }

    /**
     * @param EmployeeModel $employeeModelItem
     * @return array
     */
    private function _getRewards(EmployeeModel $employeeModelItem)
    {
        $rewards = $employeeModelItem->rewards;
        $results = [];
        foreach ($rewards as $reward) {
            $results[] = [
                'name' => $reward->name,
                'rank' => $reward->level,
                'issue_date' => $reward->issue_date,
                'expire_date' => $reward->expire_date
            ];
        }

        return $results;
    }

    /**
     * @param EmployeeModel $employeeModelItem
     * @return array
     */
    private function _getPotiticals(EmployeeModel $employeeModelItem)
    {
        $employeePolitic = $employeeModelItem->getItemRelate('politic');
        $role = $this->_getDefaultValue($employeePolitic, 'party_position');
        $role2 = $this->_getDefaultValue($employeePolitic, 'union_poisition');

        return [
            'dcsvn' => [
                'is_member' => $this->_getDefaultValue($employeePolitic, 'is_party_member'),
                'date' => $this->_getDefaultValue($employeePolitic, 'party_join_date'),
                'place' => $this->_getDefaultValue($employeePolitic, 'party_join_place'),
                'role' => [
                    'id' => $role,
                    'name' => View::getValueArray($this->partyOptions, [$role])
                ]
            ],
            'dtnvn' => [
                'is_member' => $this->_getDefaultValue($employeePolitic, 'is_union_member'),
                'date' => $this->_getDefaultValue($employeePolitic, 'union_join_date'),
                'place' => $this->_getDefaultValue($employeePolitic, 'union_join_place'),
                'role' => [
                    'id' => $role2,
                    'name' => View::getValueArray($this->partyOptions, [$role2])
                ]
            ],
        ];
    }

    /**
     * @param EmployeeModel $employeeModelItem
     * @return array
     */
    private function _getMilitary(EmployeeModel $employeeModelItem)
    {
        $employeeMilitary = $employeeModelItem->getItemRelate('military');
        $joinDate = $this->_getDefaultValue($employeeMilitary, 'join_date');
        $leftDate = $this->_getDefaultValue($employeeMilitary, 'left_date');
        $rank = $this->_getDefaultValue($employeeMilitary, 'rank');
        $position = $this->_getDefaultValue($employeeMilitary, 'position');
        $arm = $this->_getDefaultValue($employeeMilitary, 'arm');
        $revolutionJoinDate = $this->_getDefaultValue($employeeMilitary, 'revolution_join_date');
        $woundedSoldierLevel = $this->_getDefaultValue($employeeMilitary, 'wounded_soldier_level');

        return [
            'is_military' => $this->_getDefaultValue($employeeMilitary, 'is_service_man'),
            'enlistment_date' => $joinDate,
            'demobilization_date' => $leftDate,
            'rank' => [
                'id' => $rank,
                'name' => View::getValueArray($this->militaryRanks, [$rank])
            ],
            'position' => [
                'id' => $position,
                'name' => View::getValueArray($this->militaryPositions, [$position])
            ],
            'army_unit' => $this->_getDefaultValue($employeeMilitary,'branch'),
            'army' => [
                'id' => $arm,
                'name' => View::getValueArray($this->militaryArmys, [$arm])
            ],
            'demobilization_reason' => $this->_getDefaultValue($employeeMilitary,'left_reason'),
            'is_sick_solider' => $this->_getDefaultValue($employeeMilitary,'is_wounded_soldier'),
            'army_join_date' => $revolutionJoinDate,
            'classification' => [
                'id' => $woundedSoldierLevel,
                'name' => View::getValueArray($this->militaryClassifications, [$woundedSoldierLevel])
            ],
            'labor_decline_rate' => $this->_getDefaultValue($employeeMilitary,'num_disability_rate'),
            'have_regime' => $this->_getDefaultValue($employeeMilitary,'is_martyr_regime'),
        ];
    }

    /**
     * @param EmployeeModel $employeeModelItem
     * @return array
     */
    private function _getSkillsheet(EmployeeModel $employeeModelItem)
    {
        /**
         * @param EmployeeModel $employeeModelItem
         * @return EmplCvAttrValue
         */
        $funcGetSkillSheetEmployeeCV = function (EmployeeModel $employeeModelItem) {
            $employeeCvEav = EmplCvAttrValue::getAllValueCV($employeeModelItem->id);
            $collection = EmployeeProjExper::getProjExpersInCv($employeeModelItem->id);
            //===== project number =======
            $arryNumber = [];
            foreach ($collection as $iteam) {
                $arryNumber['proj_' . $iteam->id . '_number_' . $iteam->lang_code] = $iteam->number;
            }
            $employeeCvEav->eav = array_merge($employeeCvEav->eav, $arryNumber);

            return $employeeCvEav;
        };

        /**
         * @param EmplCvAttrValue $employeeCvEav
         * @return array
         */
        $funcGetSkillSheetSummary = function (EmplCvAttrValue $employeeCvEav) {
            $summaryArrays = [];
            $fields = [
                'address_home',
                'address',
                'school_graduation',
                'field_dev',
                'exper_japan',
                'statement',
                'reference',
            ];
            foreach ($fields as $field) {
                $summaryArrays[$field . '_en'] = $employeeCvEav->getVal($field . '_en');
                $summaryArrays[$field . '_ja'] = $employeeCvEav->getVal($field . '_ja');
            }

            $langSelected = $employeeCvEav->getVal('lang_ja_level');
            $summaryArrays['japanese_level'] = [
                'id' => $langSelected,
                'name' => $langSelected
            ];


            $langSelected = $employeeCvEav->getVal('lang_en_level');
            $summaryArrays['english_level'] = [
                'id' => $langSelected,
                'name' => $langSelected
            ];

            $summaryArrays['experiences'] = $employeeCvEav->getVal('exper_year');

            $roleSelected = $employeeCvEav->getVal('role');
            if (is_numeric($roleSelected)) {
                $roleSelected = [$roleSelected];
            } else {
                $roleSelected = $roleSelected ? json_decode($roleSelected, true) : [];
            }

            foreach ($roleSelected as $role) {
                $summaryArrays['main_major'][] = [
                    'id' => $role,
                    'name' => View::getValueArray($this->skillsheetRoles, [$role])
                ];
            }

            return $summaryArrays;
        };

        $funcGetSkillSheetProject = function (EmployeeModel $employeeModelItem, EmplCvAttrValue $employeeCvEav) {
            $funcGetTag = function ($projectItem, $lang) {
                $name = '';

                switch ($projectItem->type) {
                    case 'other':
                        $name = View::getValueArray($this->projectsEnviroment, [$projectItem->tag_id]);
                        break;
                    case 'lang':
                        $name = View::getValueArray($this->projectsProgramingLanguages, [$projectItem->tag_id]);
                        break;
                    case 'res':
                        $name = View::getValueArray($this->projectsPositionsLang[$lang], [$projectItem->tag_id]);
                        break;
                    case 'role':
                        $name = View::getValueArray($this->projectsRolesLang[$lang], [$projectItem->tag_id]);
                        break;
                }

                return $name;
            };

            $results = [
                'en' => [],
                'ja' => []
            ];

            if (isset($this->projectsSkillLang[$employeeModelItem->id])) {
                $projectSkillsLang = $this->projectsSkillLang[$employeeModelItem->id];
                $whiteListProjectExpTag = ['other' => 'frameworks', 'lang' => 'programming_languages', 'res' => 'assigned_phases', 'role' => 'roles'];

                foreach ($projectSkillsLang as $lang => $groupByLangs) {
                    $projects = [];
                    foreach ($groupByLangs as $projectSkillId => $groupByProjectSkillIdCollections) {
                        $projectDescription = $employeeCvEav->getVal("proj_${projectSkillId}_description_${lang}");
                        $projectName = $employeeCvEav->getVal("proj_${projectSkillId}_name_${lang}");
                        $projectNumber = $employeeCvEav->getVal("proj_${projectSkillId}_number_${lang}");
                        $projectTotalMember = $projectTotalMM = $projectStartAt = $projectEndAt = null;
                        $defaultProjectSkillArray = array_combine($whiteListProjectExpTag, array_fill(0, 4, []));
                        foreach ($groupByProjectSkillIdCollections as $type => $groupByTypeCollections) {
                            foreach ($groupByTypeCollections as $projectItem) {
                                $projectTotalMember = $projectItem->total_member;
                                $projectTotalMM = $projectItem->total_mm;
                                $projectStartAt = $projectItem->start_at;
                                $projectEndAt = $projectItem->end_at;
                                if (in_array($type, array_keys($whiteListProjectExpTag))) {
                                    $defaultProjectSkillArray[$whiteListProjectExpTag[$type]][] = [
                                        'id' => $projectItem->tag_id,
                                        'name' => $funcGetTag($projectItem, $lang)
                                    ];
                                }
                            }
                        }

                        $projects[] = array_merge([
                            'project_name' => $projectName,
                            'project_direction' => $projectDescription,
                            'project_number' => $projectNumber,
                            'total_member' => $projectTotalMember,
                            'total_MM' => $projectTotalMM,
                            'project_start_at' => $projectStartAt,
                            'project_end_at' => $projectEndAt,
                        ], $defaultProjectSkillArray);
                    }
                    $results[$lang] = $projects;
                }
            }

            return $results;
        };

        $funcGetSkillSheetSkill = function (EmployeeModel $employeeModelItem) {
            $typesSkill = ['language' => 'programming_languages', 'frame' => 'frameworks', 'database' => 'databases', 'os' => 'os'];
            $result = array_combine($typesSkill, array_fill(0, 4, []));
            if (isset($this->skillEmployees[$employeeModelItem->id])) {
                $personalSkillGroupByType = $this->skillEmployees[$employeeModelItem->id];
                foreach ($personalSkillGroupByType as $type => $personalSkillByType) {
                    if (in_array($type, array_keys($typesSkill))) {
                        foreach ($personalSkillByType as $personalSkill) {
                            $result[$typesSkill[$type]][] = [
                                'id' => $personalSkill->tag_id,
                                'name' => View::getValueArray($this->skillTagData[$type], [$personalSkill->tag_id]),
                                'rank' => $personalSkill->level,
                                'exp_year' => $personalSkill->exp_y,
                                'exp_month' => $personalSkill->exp_m,
                            ];
                        }
                    }
                }
            }

            return $result;
        };

        $employeeCvEav = $funcGetSkillSheetEmployeeCV($employeeModelItem);
        $skillshetStatus = $employeeCvEav->getVal('status');
        $summaryArrays = $funcGetSkillSheetSummary($employeeCvEav);

        return [
            'status' => [
                'id' => $skillshetStatus,
                'name' => View::getValueArray($this->skillsheetStatus, [$skillshetStatus])
            ],
            'summary' => array_merge([
                'employee_name' => $employeeModelItem->name,
                'katakana_name' => $employeeModelItem->japanese_name,
                'birthday' => $employeeModelItem->birthday,
                'gender' => $employeeModelItem->gender,
            ], $summaryArrays),
            'project' => $funcGetSkillSheetProject($employeeModelItem, $employeeCvEav),
            'skill' => $funcGetSkillSheetSkill($employeeModelItem)
        ];

    }

    /**
     * @return array
     */
    private function _getEmployees()
    {
        //Hiện tại Đang chỉ dùng Team id của HR và DN1 để test
        //Sau này lên production thì lấy hết toàn bộ team

        $teamHistoryTbl = EmployeeTeamHistory::getTableName();
        $employeeTbl = EmployeeModel::getTableName();
        $userTbl = User::getTableName();

        // <-----For Testing---->
//        $teamIdDN1 = 42;
//        $teamIdHR = 18;
//
//        $team = Team::getTeamPath($withTrashed = true);
//
//        $teamHrChild = $team[$teamIdHR]['child'];
//        $teamIdSelects = array_merge([$teamIdDN1, $teamIdHR], $teamHrChild);
//        $employees = EmployeeModel::withoutGlobalScope(new \Illuminate\Database\Eloquent\SoftDeletingScope)->select([
//            "{$employeeTbl}.*",
//            "{$userTbl}.avatar_url"
//        ])
//            ->with([
//                'familyRelationships', 'educations', 'businesses', 'scanExhibits',
//                'rewards', 'employeeCerties', 'employeeCerties.certImages'
//            ])
//            ->leftJoin($userTbl, "{$userTbl}.employee_id", '=', "{$employeeTbl}.id")
//            ->join($teamHistoryTbl, "{$teamHistoryTbl}.employee_id", '=', "{$employeeTbl}.id")
//            ->where('is_working', 1)
//            ->whereIn("{$teamHistoryTbl}.team_id", $teamIdSelects)
//            ->get();
        // <-----END Testing---->

        //TODO: For Production

        $employees = EmployeeModel::withoutGlobalScope(new \Illuminate\Database\Eloquent\SoftDeletingScope)->select([
                "{$employeeTbl}.*",
                "{$userTbl}.avatar_url"
            ])
            ->leftJoin($userTbl, "{$userTbl}.employee_id", '=', "{$employeeTbl}.id")
            ->with([
                'familyRelationships', 'educations', 'businesses', 'scanExhibits',
                'rewards', 'employeeCerties', 'employeeCerties.certImages'
            ])
            ->get();
        $employeeIds = $employees->pluck('id');
        CacheBase::put(EmployeeTeamHistory::KEY_CACHE_API_HRM_PROFILE_EMPLOYEES, $employees);
        CacheBase::put(EmployeeTeamHistory::KEY_CACHE_API_HRM_PROFILE_EMPLOYEE_IDS, $employeeIds);

        return [$employees, $employeeIds];
    }

    /**
     * @param $employees
     * @param $type
     * @param $methodName
     * @return array
     */
    private function _renderResponse($employees, $type, $methodName) {
        $results = [];
        foreach ($employees as $employee) {
            $results[] = [
                'employee_id' => $employee->id,
                $type => call_user_func_array(array($this, $methodName), [$employee])
            ];
        }

        return $results;
    }

    /**
     * @param $type
     * @return bool
     */
    public function setInitial($type)
    {
        $compactValue = [];

        switch ($type) {
            case HrmProfileCache::KEY_PERSONAL:
                $maritalStatus = EmployeeModel::labelMarital();
                $religions = EmpLib::getInstance()->relig();
                $ethnologies = EmpLib::getInstance()->folk();
                $nationalities = ViewResource::getListCountries();

                $genderOptions = EmployeeModel::labelGender();
                $relationships = RelationNames::getAllRelations();

                $compactValue = compact('maritalStatus', 'religions', 'ethnologies', 'nationalities', 'genderOptions',
                    'relationships');

                break;

            case HrmProfileCache::KEY_TEAM_HISTORY:
                $teamHistories = EmployeeTeamHistory::getTeamHistoryWithTrash();
                $teamHistories = collect($teamHistories);
                $teamHistories = $teamHistories->groupBy('employee_id')->toArray();

                $compactValue = compact('teamHistories');

                break;

            case HrmProfileCache::KEY_CONTRACT:
                $contractTypes = EmployeeWork::getAllTypeContract();
                $contractHistories = ContractModel::getContractsWithConfirm();
                $contractHistories = collect($contractHistories)->groupBy('employee_id');
                $objConfirmExpire = new ContractConfirmExpire;
                $contractStatusConfirmLabel = $objConfirmExpire->getAllLabelType();

                $compactValue = compact('contractTypes', 'contractHistories', 'contractStatusConfirmLabel');

                break;

            case HrmProfileCache::KEY_OTHER:
                $schools = School::getSchoolList();
                $schoolMajors = Major::getMajorList();
                $schoolLevels = QualityEducation::getAll();
                $schoolRanks = EmployeeSchool::listDegree();

                $cerificateStatus = Certificate::getOptionStatus();

                $partyOptions = PartyPosition::getAll();
                $unionOptions = UnionPosition::getAll();

                $compactValue = compact('schools', 'schoolMajors', 'schoolLevels', 'schoolRanks', 'cerificateStatus',
                    'partyOptions', 'unionOptions');

                break;

            case HrmProfileCache::KEY_MILITARY:
                $militaryRanks = MilitaryRank::getAll();
                $militaryPositions = MilitaryPosition::getAll();
                $militaryArmys = MilitaryArm::getAll();
                $militaryClassifications = EmployeeMilitary::toOptionSoldierLevel();

                $compactValue = compact('militaryRanks', 'militaryPositions', 'militaryArmys', 'militaryClassifications');

                break;

            case HrmProfileCache::KEY_SKILL_SHEET_SUMMARY:
                $skillsheetStatus = EmplCvAttrValue::getValueStatus();
                $skillsheetLangLevels = View::getLangLevelSplit();
                $skillsheetRoles = \Rikkei\Resource\View\getOptions::getInstance()->getRoles(true);

                $compactValue = compact('skillsheetStatus', 'skillsheetLangLevels', 'skillsheetRoles');

                break;

            case HrmProfileCache::KEY_SKILL_SHEET_PROJECT:
                $skillTagData = Tag::getTagDataProj();
                $projectsRolesLang = EmployeeProjExper::listRoles();
                $projectsPositionsLang = EmployeeProjExper::getResponsiblesDefine();
                $projectsProgramingLanguages = $skillTagData['language'];
                $projectsEnviroment = $skillTagData['dev_env'];

                $compactValue = compact('skillTagData', 'projectsRolesLang', 'projectsPositionsLang', 'projectsProgramingLanguages', 'projectsEnviroment');

                break;

            case HrmProfileCache::KEY_SKILL_SHEET_SKILL:
                $skillEmployees = $this->_initSkillEmployees();

                $compactValue = compact('skillEmployees');
                break;

            case HrmProfileCache::KEY_RESET:
                HrmProfileCache::forget();
                CacheBase::forget(EmployeeTeamHistory::KEY_CACHE_API_HRM_PROFILE_EMPLOYEE_IDS);
                CacheBase::forget(EmployeeTeamHistory::KEY_CACHE_API_HRM_PROFILE_EMPLOYEES);
                break;

            default:
                return false;
        }
        if (!empty($compactValue)) {
            HrmProfileCache::put($type, $compactValue);
        }

        return true;
    }


    /**
     * @return array
     */
    public function getProfileEmployees(Request $request, $type)
    {
        $this->_initial();

        // $employees = CacheBase::get(EmployeeTeamHistory::KEY_CACHE_API_HRM_PROFILE_EMPLOYEES);
        // if (!$employees) {
        //     list($employees,) = $this->_getEmployees();
        // }
        list($employees,) = $this->_getEmployees();

        $requestedEmployeeIds = $request->employee_ids;
        $requestedEmployeeIds = array_map(function ($item) {
            return intval($item);
        }, $requestedEmployeeIds);

        $selectedEmployees = $employees->whereIn('id', $requestedEmployeeIds);

        switch ($type) {
            case 'base':
                return $this->_renderResponse($selectedEmployees, 'profile_base', '_getProfileBase');
            case 'working-info':
                return $this->_renderResponse($selectedEmployees, 'profile_working_info', '_getProfileWorking');
            case 'contact':
                return $this->_renderResponse($selectedEmployees, 'profile_contact', '_getProfileContact');
            case 'family-information':
                return $this->_renderResponse($selectedEmployees, 'profile_family_information', '_getFamilyInformation');
            case 'education-processes':
                return $this->_renderResponse($selectedEmployees, 'education_processes', '_getEducationProcess');
            case 'working-processes':
                return $this->_renderResponse($selectedEmployees, 'working_processes', '_getWorkingProcess');
            case 'certifications':
                return $this->_renderResponse($selectedEmployees, 'certifications', '_getCertificates');
            case 'scan-exhibits':
                return $this->_renderResponse($selectedEmployees, 'scan_exhibits', '_getScanExhibits');
            case 'hobby-character':
                return $this->_renderResponse($selectedEmployees, 'hobby_character', '_getHabits');
            case 'rewards':
                return $this->_renderResponse($selectedEmployees, 'rewards', '_getRewards');
            case 'political-information':
                return $this->_renderResponse($selectedEmployees, 'political_information', '_getPotiticals');
            case 'military-information':
                return $this->_renderResponse($selectedEmployees, 'military_information', '_getMilitary');
            case 'skill-sheet':
                $this->projectsSkillLang = $this->_initSkillSheetProjectLang($requestedEmployeeIds);

                return $this->_renderResponse($selectedEmployees, 'skill_sheet', '_getSkillsheet');
        }

        return [];
    }

    /**
     * @return mixed
     */
    public function getEmployeeIds($request)
    {
        $updated_from = $request->updated_from;
        $updated_to = $request->updated_to;
        
        return $this->_getEmployeesByFilter($updated_from, $updated_to);
    }

    private function _getEmployeesByFilter($updated_from = null, $updated_to = null)
    {
        $teamHistoryTbl = EmployeeTeamHistory::getTableName();
        $employeeTbl = EmployeeModel::getTableName();
        $userTbl = User::getTableName();

        $employees = EmployeeModel::withoutGlobalScope(new \Illuminate\Database\Eloquent\SoftDeletingScope)->select([
                "{$employeeTbl}.*",
                "{$userTbl}.avatar_url"
            ])
            ->leftJoin($userTbl, "{$userTbl}.employee_id", '=', "{$employeeTbl}.id")
            ->with([
                'familyRelationships', 'educations', 'businesses', 'scanExhibits',
                'rewards', 'employeeCerties', 'employeeCerties.certImages'
            ]);
            if ($updated_from) {
                $employees = $employees->whereDate("{$employeeTbl}.updated_at", '>=', $updated_from);
            }
            if ($updated_to) {
                $employees = $employees->whereDate("{$employeeTbl}.updated_at", '<=', $updated_to);
            }
            $employees = $employees->get();
        $employeeIds = $employees->pluck('id');

        return $employeeIds;
    }
}
