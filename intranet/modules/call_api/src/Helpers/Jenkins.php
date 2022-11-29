<?php

namespace Rikkei\CallApi\Helpers;

use GuzzleHttp\Client;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Project\Model\ProjectProgramLang;
use Rikkei\Project\View\ProjectGitlab;
use Illuminate\Support\Facades\Log;

class Jenkins
{
    protected static $connect = null;
    protected static $sourceServer = null;
    protected static $project = null;
    protected static $urlWebhooks = [];

    /**
     * new Client request
     *
     * @return Client
     */
    public static function connect()
    {
        if (!self::$connect) {
            self::$connect = new Client([
                'base_uri' => CoreConfigData::getValueDb('api.jenkins.url'),
                'auth' => [
                    CoreConfigData::getValueDb('api.jenkins.auth'),
                    CoreConfigData::getValueDb('api.jenkins.token')
                ],
                'allow_redirects' => true,
                'http_errors' => false,
                'header' => [
                    'Content-Type' => 'text/xml',
                ]
            ]);
        }
        return self::$connect;
    }

    /**
     * test connect
     *
     * @return boolean
     */
    public static function isConnect()
    {
        $api = self::connect()->get('api/xml');
        if ($api->getStatusCode() == 200) {
            return true;
        }
        return Sonar::getError($api);
    }

    /**
     * get crumb jenkins for post curl
     *
     * @return boolean
     */
    public static function getCrumb()
    {
        $api = self::connect()->get('crumbIssuer/api/xml', ['query' => [
            'xpath' => 'concat(//crumbRequestField,":",//crumb)'
        ]]);
        $response = [];
        if ($api->getStatusCode() != 200) {
            $response['error'] = 1;
            $response['message'] = Sonar::getError($api);
            return $response;
        }
        $response['success'] = 1;
        $response['message'] = preg_replace('/\:/', ' = ', $api->getBody()->getContents());
        return $response;
    }

    /**
     * create project with key
     *
     * @param string $projectKey
     * @return type
     */
    public static function create()
    {
        $projectKey = self::$sourceServer->id_jenkins;
        if (!$projectKey) {
            $response['success'] = 0;
            $response['message'] = trans('call_api::message.Empty project jenkins key');
            return $response;
        }
        // not exist project gitlab
        if (!ProjectGitlab::getInstance()->isProjectExists(self::$sourceServer->id_git)) {
            $response['success'] = 0;
            $response['message'] = trans('call_api::message.Gitlab project is not exists');
            return $response;
        }
        $urlPathDev = config('project.sonar.jenkins.path_suffix_dev');
        $urlPathPre = config('project.sonar.jenkins.path_suffix_preview');
        self::$urlWebhooks = [];
        $result = self::createProject($projectKey . $urlPathDev, $urlPathDev);
        if (is_string($result)) {
            $response['success'] = 0;
            $response['message'] = $result;
            return $response;
        }
        $result2 = self::createProject($projectKey . $urlPathPre, $urlPathPre);
        if ($result === null && $result2 === null) {
            $response['success'] = 1;
            $response['message'] = trans('call_api::message.Jenkins project exists');
            return $response;
        }
        // create gitlab webhooks
        if (self::$urlWebhooks) {
            $hook = self::gitlabWebhooks();
        }
        $response['success'] = 1;
        $response['message'] = trans('call_api::message.Create jenkins project success');
        if (!$hook) {
            $response['message'] .= '<br/>' . trans('call_api::message.Error create webhook in gitlab');
        }
        return $response;
    }

    /**
     * check job (folder or project) exists
     *
     * @param string $jobKey
     * @return boolean
     */
    public static function isJobExists($jobKey)
    {
        $api = self::connect()->get($jobKey . '/config.xml');
        if ($api->getStatusCode() == 200) {
            return true;
        }
        return false;
    }

    /**
     * create project jenkins
     *
     * @param string $projectKey project id_jenkins
     * @param string $typeMode develop|preview
     * @return boolean
     */
    public static function createProject($projectKey, $typeMode)
    {
        if (self::isJobExists($projectKey)) {
            return null;
        }
        $urlPath = preg_split('/\//', $projectKey);
        $countUrlPath = count($urlPath);
        $crumLabel = CoreConfigData::getValueDb('api.jenkins.crumb');
        $crumVal = CoreConfigData::getValueDb('api.jenkins.crumb_val');
        $connect = self::connect();
        $urlWebhook = '';
        if ($countUrlPath === 1) {
            $folder = '';
            $projectId = $projectKey;
            $urlWebhook = $projectId;
        } else {
            $folder = '';
            $urlPathPrefix = config('project.sonar.jenkins.path_prefix');
            foreach ($urlPath as $i => $projectId) {
                if ($i % 2 === 0) {
                    continue;
                }
                if (($countUrlPath === $i + 1)) {
                    break;
                }
                if (self::isJobExists($folder . $urlPathPrefix . $projectId)) {
                    $folder .= $urlPathPrefix . $projectId . '/';
                    $urlWebhook .= $projectId . '/';
                    continue;
                }
                // create folder
                $api = $connect->post($folder.'createItem', ['form_params' => [
                    'name' => $projectId,
                    'mode' => 'com.cloudbees.hudson.plugins.folder.Folder',
                    'from' => '',
                    'json' => '{"name":"'.$projectId.'","mode":"com.cloudbees.hudson.plugins.folder.Folder","from":"","Submit":"OK"}',
                    'Submit' => 'OK'
                ], 'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    $crumLabel => $crumVal
                ]]);
                $folder .= $urlPathPrefix . $projectId . '/';
                $urlWebhook .= $projectId . '/';
                if ($api->getStatusCode() != 200) {
                    return Sonar::getError($api);
                }
            }
        }
        $program = self::getProgramProject();
        if (!$program) {
            return trans('call_api::message.Programming language of project is not in the set php, java, android and c#');
        }
        $programMode = $program.$typeMode;
        $fileConfigPath = resource_path("assets/template/sonar/jenkins-proj-{$programMode}-config.xml");
        if (!file_exists($fileConfigPath)) {
            return trans('call_api::message.Not found jenkins config file');
        }
        $gitlabLink = CoreConfigData::getValueDb('project.gitlab_api_project_url');
        if ($gitlabLink) {
            $parseUrl = parse_url($gitlabLink);
            if (isset($parseUrl['host'])) {
                $gitlabLink = sprintf('git@%s:%s.git', $parseUrl['host'], self::$sourceServer->id_git);
            }
        }
        $xml = simplexml_load_file($fileConfigPath);
        $xml->scm->userRemoteConfigs->{'hudson.plugins.git.UserRemoteConfig'}->url = $gitlabLink;
        $buildProperty = config('project.sonar.jenkins.build_properties.'.$programMode);
        if ($buildProperty) {
            $buildProperty = (array) $buildProperty;
            if (isset($buildProperty['sonar.gitlab.project_id'])) {
                $buildProperty['sonar.gitlab.project_id'] = self::$sourceServer->id_git;
            }
            if (isset($buildProperty['sonar.projectKey'])) {
                $buildProperty['sonar.projectKey'] = self::$sourceServer->id_sonar;
            }
            if (isset($buildProperty['sonar.projectName'])) {
                $buildProperty['sonar.projectName'] = self::$project->name;
            }
            $newPro = '';
            foreach ($buildProperty as $key => $value) {
                $newPro .= $key . '=' . $value . PHP_EOL;
            }
            if ($newPro) {
                $buildProperty = substr($newPro, 0, -strlen(PHP_EOL));
            }
        }
        $pathXmlBuilder = config('project.sonar.jenkins.node_plugin_xml');
        if (isset($pathXmlBuilder[$program])) {
            $path = $pathXmlBuilder[$program]['plugin'];
            $xml->builders->{$path}->{$pathXmlBuilder[$program]['property']} = $buildProperty;
        }
        $api = $connect->post($folder . 'createItem?name='.$projectId, [
            'body' => $xml->asXML(),
            'headers' => [
                'Content-Type' => 'text/xml; charset=UTF8',
                $crumLabel => $crumVal
            ]
        ]);
        if ($api->getStatusCode() != 200) {
            return Sonar::getError($api);
        }
        self::$urlWebhooks[$typeMode] = $urlWebhook . $projectId;
        return true;
    }

    /**
     * set value variable
     *
     * @param object $project
     * @param object $sourceServer
     */
    public static function setValue($project = null, $sourceServer = null)
    {
        if ($project !== null) {
            self::$project = $project;
        }
        if ($sourceServer !== null) {
            self::$sourceServer = $sourceServer;
        }
    }

    /**
     * get flag program of project
     *
     * @return boolean|string
     */
    public static function getProgramProject()
    {
        if (!self::$project) {
            return false;
        }
        $program = ProjectProgramLang::getProgramLangOfProject(self::$project);
        $arrayFind = config('project.sonar.jenkins.program_to_flag');
        foreach ($arrayFind as $find => $flag) {
            if (preg_grep($find . 'i', $program)) {
                return $flag;
            }
        }
        return false;
    }

    /**
     * create hook for project
     *
     * @param type $devFlag
     * @param type $preFlag
     * @return boolean
     */
    public static function gitlabWebhooks()
    {
        $urlJenkinsProj = CoreConfigData::getValueDb('api.jenkins.url')
            . config('project.sonar.jenkins.path_project_prefix');
        try {
            foreach (self::$urlWebhooks as $type => $url) {
                ProjectGitlab::getInstance()->getApi()->api('projects')->addHook(
                    self::$sourceServer->id_git,
                    $urlJenkinsProj . $url,
                    (array) config('project.sonar.gitlab.' . $type)
                );
            }
            return true;
        } catch (Exception $ex) {
            Log::error($ex);
            return false;
        }
    }
}
