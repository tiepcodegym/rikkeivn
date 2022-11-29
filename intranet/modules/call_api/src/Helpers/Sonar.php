<?php

namespace Rikkei\CallApi\Helpers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Rikkei\Core\Model\CoreConfigData;

class Sonar
{
    protected static $connect = null;

    /**
     * new Client request
     *
     * @return Client
     */
    public static function connect()
    {
        if (!self::$connect) {
            self::$connect = new Client([
                'base_uri' => CoreConfigData::getValueDb('api.sonar.url') . '/api/',
                'auth' => [CoreConfigData::getValueDb('api.sonar.token'), ''],
                'allow_redirects' => true,
                'http_errors' => false,
            ]);
        }
        return self::$connect;
    }

    /**
     * write log of sonar
     *
     * @param object $api
     */
    public static function errorLog($api)
    {
        Log::error('Sonar: ' . $api->getStatusCode()
            . ' - ' . $api->getReasonPhrase()) .
            ' - ' . $api->getBody()->getContents();
    }

    /**
     * test connect
     *
     * @return boolean
     */
    public static function isConnect()
    {
        $api = self::connect()->get('system/health');
        if ($api->getStatusCode() == 200) {
            return true;
        }
        return self::getError($api);
    }

    /**
     * get error message
     *
     * @param object $api
     * @return string
     */
    public static function getError($api)
    {
        return $api->getStatusCode() . ' - ' . $api->getReasonPhrase();
    }

    /**
     * check exists user
     *
     * @param string $account
     * @return boolean|array: true -> exists, false -> not exists, array -> error
     */
    public static function isUserExists($account)
    {
        $api = self::connect()->get('users/search', ['query' => [
            'q' => $account,
        ]]);
        if ($api->getStatusCode() != 200) {
            $response['error'] = 1;
            $response['message'] = Sonar::getError($api);
            return $response;
        }
        $body = json_decode($api->getBody()->getContents());
        if (!$body->paging->total) {
            return false;
        }
        foreach ($body->users as $user) {
            if ($user->login == $account) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if project exists
     *
     * @param string $projectKey get project in exec function
     * @return boolean|array
     * true if project exists
     * array if error
     */
    public static function isProjectExist($projectKey)
    {
        $response = [];
        $api = self::connect()->get('projects/search', [ 'query' => [
            'projects' => $projectKey
        ]]);
        if ($api->getStatusCode() != 200) {
            $response['error'] = 1;
            $response['message'] = Sonar::getError($api);
            return $response;
        }
        $body = json_decode($api->getBody()->getContents());
        if (!$body->paging->total) {
            return false;
        }
        foreach ($body->components as $item) {
            if ($item->key == $projectKey) {
                return true;
            }
        }
        return false;
    }
}
