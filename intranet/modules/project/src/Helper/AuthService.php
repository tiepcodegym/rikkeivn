<?php
namespace Rikkei\Project\Helper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class AuthService
{
    protected $baseurl;
    protected $token;

    public function  __construct() {
        $this->baseurl = config('project.base_url');
        $this->baseurl = rtrim($this->baseurl, '/') . '/';  // ensure trailing slash
    }

    public function makeRequest() {
        $client = new Client([
            'headers'  => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->token
            ],
            'base_uri' => $this->baseurl
        ]);

        return $client;
    }

    public function loginGetToken(){
        $password = config('project.password');
        $email = config('project.email');

        $payload = (object)[
            'password'=>(string)$password,
            'email'=>(string)$email
        ];

        $retval = $this->login($payload);

        return $retval;
    }

    /**
     * Send a request to api service method Post
     */
    private function login($payload)
    {
        try {
            $response = $this->makeRequest()->post('auth/signin', ['json' => $payload])->getBody();
            $dataJson = json_decode($response->getContents());

            if(!is_null($dataJson->result)) {
                if ($dataJson->statusCode == 200) {
                    $this->token = $dataJson->result->token;

                    return $this->checkCode($dataJson);
                }
            }

            return $this->checkCode($dataJson);
        } catch(ClientException $e) {
            return $this->error($e->getCode(), 'Unauthorized');
        }
    }

    /**
     * Send a request to api get list soft ware
     */
    public function getSoftware()
    {
        try {
            $response = $this->makeRequest()->post('accounting/software-cost/list')->getBody();
            $dataJson = json_decode($response->getContents());

            if(!is_null($dataJson->result)) {
                if ($dataJson->statusCode == 200) {
                    return $this->checkCode($dataJson);
                }
            }

            return $this->checkCode($dataJson);
        } catch(ClientException $e) {
            return $this->error($e->getCode(), 'Unauthorized');
        }
    }


    public function checkCode($dataJson)
    {
        switch ($dataJson->statusCode) {
            case 401:
                return $this->error($dataJson->statusCode, $dataJson->message);
                break;
            case 400:
                return $this->error($dataJson->statusCode, $dataJson->message);
                break;
            case 404:
                return $this->error($dataJson->statusCode, $dataJson->message);
                break;
            case 500:
                return $this->error($dataJson->statusCode, $dataJson->message);
                break;
            default:
                return $this->success($dataJson->message, $dataJson->result);
        }
    }

    public function success($message, $data)
    {
        return ['statusCode' => 200, 'message' => $message, 'result' => $data];
    }

    public function error($code, $message)
    {
        return ['statusCode' => $code, 'message' => $message];
    }
}
