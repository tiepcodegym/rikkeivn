<?php

namespace Rikkei\Project\Model;

use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\ProjectWOBase;
use DB;
use Lang;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\CacheHelper;
use Exception;
use Illuminate\Support\Str;

class SourceServer extends CoreModel
{
    const KEY_CACHE = 'pp_source_server';
    
    /*
     * const checked 
     */
    const IS_CHECKED = 1;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'source_server';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id_redmine', 'id_git', 'id_svn'];

    /*
     * get all assumption constrain by project id
     * @param int
     * @return collection
     */
    public static function getSourceServer($projectId)
    {
        if ($item = CacheHelper::get(self::KEY_CACHE, $projectId)) {
            return $item;
        }
        $item = self::select([
            'id',
            'project_id',
            'id_redmine', 
            'id_git', 
            'id_svn', 
            'is_check_redmine', 
            'is_check_git', 
            'is_check_svn',
            'id_redmine_external',
            'id_git_external',
            'id_svn_external',
            'id_sonar',
            'id_jenkins',
        ])
            ->where('project_id', $projectId)
            ->first();
        if (!$item) {
            return new self;
        }
        CacheHelper::put(self::KEY_CACHE, $item, $projectId);
        return $item;
    }
    
    /**
     * check sync source server
     * 
     * @param int $projectId
     * @return array
     */
    public static function getSyncSourceServer($projectId, $sourceServer = null)
    {
        if (!$sourceServer) {
            $sourceServer = self::getSourceServer($projectId);
        }
        if (!$sourceServer) {
            return [
                'git' => false,
                'redmine' => false,
                'svn' => false
            ];
        }
        return [
            'git' => ($sourceServer->is_check_git && $sourceServer->id_git) ? true :  false,
            'redmine' => ($sourceServer->is_check_redmine && $sourceServer->id_redmine) ? true :  false,
            'svn' => ($sourceServer->is_check_svn && $sourceServer->id_svn) ? true :  false
        ];
    }

    /*
     * get all assumption constrain by project id
     * @param int
     * @return collection
     */
    public static function getSourceServerDraft($projectId)
    {
        $item = self::select(['id', 'id_redmine', 'id_git', 'id_svn', 'is_check_redmine', 'is_check_git', 'is_check_svn', 'status'])
            ->where('project_id', $projectId)
            ->where('status', '!=', ProjectWOBase::STATUS_APPROVED)
            ->first();
        return $item;
    }


    /**
     * label of field
     * 
     * @return array
     */
    public static function lablelFieldSourceServer()
    {
        return [
            'id_redmine' => Lang::get('project::view.Redmine indentify'),
            'id_git' => Lang::get('project::view.Git indentify'),
            'id_svn' => Lang::get('project::view.Snv indentify'),
            'is_check_redmine' => Lang::get('project::view.Check redmine'),
            'is_check_git' => Lang::get('project::view.Check git'),
            'is_check_svn' => Lang::get('project::view.Check svn'),
        ];
    }

     /**
     * referesh data source server
     * @param int
     * @param int
     */
    public static function refreshDataSourceServer($statusTask, $projectId)
    {
        DB::beginTransaction();
        try {
            $sourceServerDraft = self::getSourceServerDraft($projectId);
            if ($sourceServerDraft) {
                if ($statusTask == Task::STATUS_APPROVED) {
                    $sourceServer = self::getSourceServer($projectId);
                    $sourceServerDraft->parent_id = null;
                    $sourceServerDraft->save();
                    if ($sourceServer) {
                        $sourceServer->delete();
                    }
                    $sourceServerDraft->status = ProjectWOBase::STATUS_APPROVED;
                    $sourceServerDraft->task_id = null;
                    $sourceServerDraft->save();
                } else if ($statusTask == Task::STATUS_FEEDBACK) {
                    $sourceServerDraft->status = ProjectWOBase::STATUS_FEEDBACK;
                    $sourceServerDraft->save();
                } else if ($statusTask == Task::STATUS_REVIEWED) {
                    $sourceServerDraft->status = ProjectWOBase::STATUS_REVIEWED;
                    $sourceServerDraft->save();
                }
            }
            DB::commit();
            return true;
        } catch (Exception $ex) {
            DB::rollback();
            return false;
        }
    }

    /**
     * check exists source server
     * @param array
     * @return boolean
     */
    public static function checkExists($input)
    {
        $query = self::where($input['name'], $input['value']);
        if (isset($input['projectId'])) {
            $sourceServer = self::getSourceServer($input['projectId']);
            $sourceServerDraft = self::getSourceServerDraft($input['projectId']);
            // add new
            if (!$sourceServer && !$sourceServerDraft) {
                $isCheck = $query->get();
                if (count($isCheck)) {
                    return 'false';
                }
            // update draft (feedback)       
            } else if (!$sourceServer && $sourceServerDraft) {
                $isCheck = $query->whereNotIn('id', [$sourceServerDraft->id])->get();
                if (count($isCheck)) {
                    return 'false';
                }
            // update draft for edit approved
            } else if ($sourceServer && $sourceServerDraft) {
                $isCheck = $query->whereNotIn('id', [$sourceServerDraft->id, $sourceServer->id])->get();
                if (count($isCheck)) {
                    return 'false';
                }
            //add draft for edit approved                        
            } else {
                $isCheck = $query->whereNotIn('id', [$sourceServer->id])->get();
                if (count($isCheck)) {
                    return 'false';
                }
            }
        } else {
            $isCheck = $query->get();
            if (count($isCheck)) {
                return 'flase';
            }
        }
        return 'true';
    }
    
    /**
     * rewrite save model
     * 
     * @param array $options
     */
    public function save(array $options = array()) 
    {
        try {
            $result = parent::save($options);
            CacheHelper::forget(self::KEY_CACHE, $this->project_id);
            return $result;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * save source server from request
     * 
     * @param object $project
     * @param string $fullTeamName
     */
    public static function saveFromRequest($project, $fullTeamName = null)
    {
        $sourceServer = self::where('project_id', $project->id)
            ->first();
        if (!$sourceServer) {
            $sourceServer = new self;
        }
        $isChange = false;
        if (!$sourceServer->id_redmine) {
            $sourceServer->id_redmine = Str::slug($project->project_code_auto, '_');
            $isChange = true;
        }
        if (!$sourceServer->id_git) {
            if (!$fullTeamName) {
                Project::getOnlyTeamName($project, $fullTeamName);
            }
            //$fullTeamName = Str::slug($fullTeamName);
            $sourceServer->id_git = Str::slug($fullTeamName, '_')
                . '/' . Str::slug($project->project_code_auto, '_');
            $isChange = true;
        }
        if (!$sourceServer->id_svn) {
            $sourceServer->id_svn = Str::slug($project->project_code_auto, '_');
            $isChange = true;
        }
        if (!$sourceServer->id_sonar) {
            $sourceServer->id_sonar = Str::slug($project->project_code_auto, '_');
            $isChange = true;
        }
        if (!$sourceServer->id_jenkins) {
            $urlPathPrefix = config('project.sonar.jenkins.path_prefix');
            if (!$fullTeamName) {
                Project::getOnlyTeamName($project, $fullTeamName);
            }
            $sourceServer->id_jenkins = $urlPathPrefix . Str::slug($fullTeamName, '_')
                . '/' . $urlPathPrefix . Str::slug($project->project_code, '_')
                . '/' . $urlPathPrefix . Str::slug($project->project_code_auto, '_');
            $isChange = true;
        }
        if ($isChange) {
            $sourceServer->is_check_redmine = self::IS_CHECKED;
            $sourceServer->is_check_git = self::IS_CHECKED;
            /*$ss = (array) Input::get('ss');
            unset($ss['id_redmine']);
            unset($ss['id_git']);
            unset($ss['id_svn']);
            $sourceServer->setData($ss);*/
            $sourceServer->project_id = $project->id;
            $sourceServer->save();
        }
        return $sourceServer;
    }

    /**
     * edit basic info
     * @param array
     * @param array
     * @return array
     */
    public static function editBasicInfo($data, $project)
    {
        $result = array();
        $result['status'] = false;
        $sourceServer = self::where('project_id', $project->id)->first();
        $sourceServer->{$data['name']} = $data['value'];
        if($sourceServer->save()) {
            $result['status'] = true;
        }
        CacheHelper::forget(self::KEY_CACHE, $project->id);
        return $result;
    }
}