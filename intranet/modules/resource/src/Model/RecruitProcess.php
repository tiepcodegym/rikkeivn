<?php
namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Resource\Model\Candidate;
use Lang;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\Team;
use Rikkei\Resource\View\getOptions;
use Rikkei\Resource\Model\Channels;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Team\Model\Employee;

class RecruitProcess extends CoreModel
{
    
    protected $table = 'recruit_process';
    
    const KEY_CACHE = 'recruit_process';
    
    /**
     *  store this object
     * @var object
     */
    protected static $instance;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['candidate_id', 'action', 'note'];
    
    protected $fieldResult = 'note';

    protected $ignore = ['_token', 'languages', 'programs', 'chk_interviewer', 'requests', 'teams', 'positions',
                        'candidate_id', 'chk_lang', 'chk_pro', 'detail', 'test_option_type_ids', 'check_team', 'check_pos'];
    
    function __construct( )
    {
        $this->ignore[] = $this->fieldResult;
    }
    
    /**
     * save differences history
     * @param array $data 
     */
    public function saveProcess($data, $curEmpId = null) {
        $model = new RecruitProcess();
        $model->candidate_id = $data['candidate_id'];
        $model->owner = isset(Permission::getInstance()->getEmployee()->id) ? Permission::getInstance()->getEmployee()->id : $curEmpId;
        if (isset($data['create'])) {
            $model->action = json_encode([Lang::get('resource::view.Candidate.Create.Create candidate')]);
            $model->save();
        } else {
            $candidate = Candidate::find($data['candidate_id']);
            $diff = [];
            //find diffrences
            foreach ($data as $key => $value) {
                if (!in_array($key, $this->ignore) && $value != $candidate->$key) { 
                    $diff[] = $this->getAction($key, $candidate->$key, $value);
                } 
            }
            
            //Check differences languages and programs language
            if (isset($data['programs'])) {
                $checkDiffPro = $this->checkDiffProgram($candidate, $data['programs']);
            }
            if (isset($data['languages'])) {
                $checkDiffLang = $this->checkDiffLang($candidate, $data['languages']);
            }
            if (isset($data['requests'])) {
                $checkDiffRequest = $this->checkDiffRequest($candidate, $data['requests']);
            }
            if (isset($data['teams'])) {
                $checkDiffTeam = $this->checkDiffTeam($candidate, $data['teams']);
            }
            if (isset($data['positions'])) {
                $checkDiffPos = $this->checkDiffPos($candidate, $data['positions']);
            }
            //if have any diffrence then save history action
            if (count($diff) 
                    || (isset($checkDiffPro) && $checkDiffPro) 
                    || (isset($checkDiffLang) && $checkDiffLang)
                    || (isset($checkDiffRequest) && $checkDiffRequest)
                    || (isset($checkDiffTeam) && $checkDiffTeam)
                    || (isset($checkDiffPos) && $checkDiffPos)) {
                // diff programming languages
                if (isset($checkDiffPro) && $checkDiffPro) { 
                    $oldPrograms = Candidate::getAllProgramOfCandidate($candidate);
                    $oldProsName = implode(', ', Programs::getInstance()->getNamesByIds($oldPrograms));
                    $newProsName = implode(', ', Programs::getInstance()->getNamesByIds($data['programs']));
                    $action = 'programs: ' . $oldProsName . ' -> ' . $newProsName;
                    $diff[] = $action;
                }
                // diff languages
                if (isset($checkDiffLang) && $checkDiffLang) { 
                    $oldLangs = Candidate::getAllLangOfCandidate($candidate);
                    $oldLangsName = implode(', ', Languages::getInstance()->getNamesByIds($oldLangs));
                    $newLangsName = implode(', ', Languages::getInstance()->getNamesByIds($data['languages']));
                    $action = 'languages: ' . $oldLangsName . ' -> ' . $newLangsName;
                    $diff[] = $action;
                }
                // diff requests
                if (isset($checkDiffRequest) && $checkDiffRequest) { 
                    $oldRequests = Candidate::getAllRequestOfCandidate($candidate);
                    $oldRequestsName = implode(', ', ResourceRequest::getInstance()->getTitlesByIds($oldRequests));
                    $newRequestsName = implode(', ', ResourceRequest::getInstance()->getTitlesByIds($data['requests']));
                    $action = 'requests: ' . $oldRequestsName . ' -> ' . $newRequestsName;
                    $diff[] = $action;
                }
                // diff teams
                if (isset($checkDiffTeam) && $checkDiffTeam) { 
                    $oldTeams = Candidate::getAllTeamOfCandidate($candidate);
                    $oldTeamsName = implode(', ', Team::getNamesByIds($oldTeams));
                    $newTeamsName = implode(', ', Team::getNamesByIds($data['teams']));
                    $action = 'teams: ' . $oldTeamsName . ' -> ' . $newTeamsName;
                    $diff[] = $action;
                }
                // diff positions
                if (isset($checkDiffPos) && $checkDiffPos) { 
                    $oldPos = CandidatePosition::getPositionIds($candidate->id);
                    $oldPosName = [];
                    $newPosName = [];
                    foreach ($oldPos as $old) {
                        $oldPosName[] = getOptions::getInstance()->getRole($old);
                    }
                    foreach ($data['positions'] as $new) {
                        $newPosName[] = getOptions::getInstance()->getRole($new);
                    }
                    $action = 'positions: ' . implode(',', $oldPosName) . ' -> ' . implode(',', $newPosName);
                    $diff[] = $action;
                }
                if (isset($data['note']) && !empty(trim($data['note']))) {
                    $model->note = $data['note'];
                }
                if (count($diff)) {
                    $model->action = json_encode($diff);
                }
                $model->save();
            }
        }
    }
    
    public static function getInstance()
    {
        if (! isset(self::$instance)) {
            self::$instance = new static;
        }
        return self::$instance;
    }
    
    /**
     * Check diffences program languages
     * @param Rikkei\Resource\Model\Candidate $candidate
     * @return boolean
     */
    public function checkDiffProgram($candidate, $programs) {
        $oldPrograms = Candidate::getAllProgramOfCandidate($candidate);
        $checkDiff = false;
        if (count($oldPrograms) != count($programs)) {
            $checkDiff = true;
        } else {
            foreach ($programs as $pro) {
               if (!in_array($pro, $oldPrograms)) {
                   $checkDiff = true;
               }
            }
        }
        
        return $checkDiff;
    }
    
    /**
     * Check diffences languages
     * @param Rikkei\Resource\Model\Candidate $candidate
     * @return boolean
     */
    public function checkDiffLang($candidate, $langs) {
        $oldLangs = Candidate::getAllLangOfCandidate($candidate);
        $checkDiff = false;
        if (count($oldLangs) != count($langs)) {
            $checkDiff = true;
        } else {
            foreach ($langs as $lang) {
               if (!in_array($lang, $oldLangs)) {
                   $checkDiff = true;
               }
            }
        }
        
        return $checkDiff;
    }
    
    /**
     * Check diffences requests
     * 
     * @param Rikkei\Resource\Model\Candidate $candidate
     * @return boolean
     */
    public function checkDiffRequest($candidate, $requests) {
        $oldRequests = Candidate::getAllRequestOfCandidate($candidate);
        if (count($oldRequests) != count($requests)) {
            return true;
        } else {
            foreach ($requests as $request) {
               if (!in_array($request, $oldRequests)) {
                   return true;
               }
            }
        }
        
        return false;
    }
    
    /**
     * Check diffences teams
     * 
     * @param Rikkei\Resource\Model\Candidate $candidate
     * @return boolean
     */
    public function checkDiffTeam($candidate, $teams) {
        $oldTeams = Candidate::getAllTeamOfCandidate($candidate);
        if (count($oldTeams) != count($teams)) {
            return true;
        } else {
            foreach ($teams as $team) {
               if (!in_array($team, $oldTeams)) {
                   return true;
               }
            }
        }
        
        return false;
    }
    
    /**
     * Check diffences positions
     * 
     * @param Rikkei\Resource\Model\Candidate $candidate
     * @return boolean
     */
    public function checkDiffPos($candidate, $positions) {
        $oldPos = CandidatePosition::getPositionIds($candidate->id);
        if (count($oldPos) != count($positions)) {
            return true;
        } else {
            foreach ($positions as $pos) {
               if (!in_array($pos, $oldPos)) {
                   return true;
               }
            }
        }
        
        return false;
    }
    
    /**
     * Return action
     * @param string $key
     * @param int $oldVal
     * @param int $newVal
     * @return string
     */
    public function getAction($key, $oldVal, $newVal) {
        $action = '';
        switch ($key) {
            case 'team_id' :
                $oldTeam = Team::getTeamById($oldVal);
                $newTeam = Team::getTeamById($newVal);
                $oldTeamName = $oldTeam ? $oldTeam->name : "''";
                $newTeamName = $newTeam ? $newTeam->name : "''";
                $action = 'team: ' . $oldTeamName . ' -> ' . $newTeamName; 
                break;
            case 'status' :
                $oldStatus = getOptions::getInstance()->getCandidateStatus($oldVal);
                $newStatus = getOptions::getInstance()->getCandidateStatus($newVal);
                $action = $key . ': ' . $oldStatus . ' -> ' . $newStatus; 
                break;
            case 'channel_id' :
                $oldChannel = Channels::getChannelById($oldVal);
                $newChannel = Channels::getChannelById($newVal);
                if ($oldChannel) {
                   $action = 'channel: ' . $oldChannel->name . ' -> ' . $newChannel->name; 
                } else {
                   $action = 'channel: \'\' -> ' . $newChannel->name; 
                }
                
                break;
            case 'position_apply' :
                $oldPosition = getOptions::getInstance()->getRole($oldVal);
                $newPosition = getOptions::getInstance()->getRole($newVal);
                $action = 'position apply: ' . $oldPosition . ' -> ' . $newPosition; 
                break;
            case 'contact_result' :
                $oldResult = getOptions::getInstance()->getResult($oldVal);
                $newResult = getOptions::getInstance()->getResult($newVal);
                $action = 'contact result: ' . $oldResult . ' -> ' . $newResult; 
                break;
            case 'test_result':
                $oldResult = getOptions::getInstance()->getResult($oldVal);
                $newResult = getOptions::getInstance()->getResult($newVal);
                $action = 'test result: ' . $oldResult . ' -> ' . $newResult; 
                break;
            case 'interview_result':
                $oldResult = getOptions::getInstance()->getResult($oldVal);
                $newResult = getOptions::getInstance()->getResult($newVal);
                $action = 'interview result: ' . $oldResult . ' -> ' . $newResult; 
                break;
            case 'offer_result':
                $oldResult = getOptions::getInstance()->getResult($oldVal);
                $newResult = getOptions::getInstance()->getResult($newVal);
                $action = 'offer result: ' . $oldResult . ' -> ' . $newResult; 
                break;
            case 'interviewer':
                $oldVal = explode(',', $oldVal);
                $interviewers = Employee::getEmpByIds($oldVal);
                $old = [];
                foreach ($interviewers as $item) {
                    $old[] = $item->email;
                }
                if (count($old)) {
                    $old = implode(', ', $old);
                } else {
                    $old = '';
                }
                $interviewers = Employee::getEmpByIds($newVal);
                $new = [];
                foreach ($interviewers as $item) {
                    $new[] = $item->email;
                }
                $action = 'interviewer: ' . $old . ' -> ' . implode(', ', $new); 
                break;
            default : 
                if (is_array($oldVal)) {
                    $oldVal = implode(',', $oldVal);
                }
                if (is_array($newVal)) {
                    $newVal = implode(',', $newVal);
                }
                $action = $key . ': ' . $oldVal . ' -> ' . $newVal; 
        }
        
        return $action;
    }
    
    public function getList($order, $dir) {
        $self = self::getTableName();
        $empTable = \Rikkei\Team\Model\Employee::getTableName();
        $collection = self::join("{$empTable}", "{$self}.owner", '=', "{$empTable}.id")
                    ->orderBy($order,$dir);
        
        $collection->select("{$self}.*", "{$empTable}.name");
        
        return $collection;
    }
}