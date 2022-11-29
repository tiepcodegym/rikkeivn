<?php

namespace Rikkei\Api\Sync;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

/**
 * Description of BaseSync
 *
 * @author lamnv
 */
abstract class BaseSync
{
    const STT_SUCCESS = 'success';
    const STT_ERROR = 'error';
    const CODE_SUCCESS = 200;
    const CODE_AUTH = 401;
    const CODE_SERVER_ERROR = 500;
    const API_CALL = [
        'employees' => 'EmployeeSync'
    ];

    public static function call($table, $event, $data)
    {
        $apiCall = self::API_CALL;
        if (isset($apiCall[$table])) {
            return call_user_func('\Rikkei\Api\Sync\\' . $apiCall[$table] . '::callEvent', $event, $data);
        }
        return null;
    }

    public static function callEvent($event, $data)
    {
        //code extends
    }

    /**
     * request api
     *
     * @param string $url
     * @param string $method (post, get, delete...)
     * @param array|json $data
     * @param array $optHeader
     * @return array
     */
    public static function callApi($url, $method = 'POST', $data = null, $optHeader = [])
    {
        $client = new Client();
        $token = isset($optHeader['authToken']) ? $optHeader['authToken'] : null;
        $actorId = isset($optHeader['userId']) ? $optHeader['userId'] : null;
        try {
            $headers = [
                'Content-type' => 'application/json'
            ];
            if ($token) {
                $headers['X-Auth-Token'] = $token;
            }
            if ($actorId) {
                $headers['X-User-Id'] = $actorId;
            }
            $response = $client->request($method, $url, [
                'headers' => $headers,
                'body' => json_encode($data)
            ]);
            if ($response->getStatusCode() == self::CODE_SUCCESS) {
                $responseData = $response->getBody()->getContents();
                Log::info('call: ' . $url . ' response: ' . $responseData);
                $result = json_decode($responseData, true);
                $result['status'] = self::STT_SUCCESS;
                return $result;
            }
            return [
                'status' => 'error',
                'message' => trans('core::message.Error system, please try later!'),
                'code' => self::CODE_SERVER_ERROR
            ];
        } catch (\Exception $ex) {
            return [
                'status' => 'error',
                'message' => $ex->getMessage() . ' - ' . $ex->getFile() . ' - '. $ex->getLine(),
                'code' => $ex->getCode()
            ];
        }
    }

}
