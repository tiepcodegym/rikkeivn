<?php


namespace Rikkei\HomeMessage\Helper;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Psr\Http\Message\ResponseInterface;

class RikkeiAppBackendApiConnect
{
    protected $client;
    protected $headers;
    protected $body;
    protected $uriBase;
    protected $uriRequest;
    static protected $instance;

    public function __construct()
    {
        $this->client = new Client();
        $config = config('api.rikkei_app_backend');
        $this->uriBase = $config['BASE_HOST'];
        $logLogin = DB::table('log_logins')->where('employee_id', Auth::id())->orderBy('created_at', 'DESC')->first();
        $accessToken = !empty($logLogin) ? 'Bearer ' . $logLogin->access_token : '';
        $this->headers = [
            'api-secret' => $config['SECRET'],
            'Content-type' => 'application/json',
            'Authorization' => $accessToken,
        ];
    }

    /**
     * @return RikkeiAppBackendApiConnect
     */
    public static function makeInstance()
    {
        if (self::$instance) {
            return self::$instance;
        }
        self::$instance = new self();
        return self::$instance;
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers = [])
    {
        $headersTmp = $this->headers;
        foreach ($headers as $k => $v) {
            $headersTmp [$k] = $v;
        }
        $this->headers = $headersTmp;
        return $this;
    }

    /**
     * @param array $body
     * @return $this
     */
    public function setBody(array $body = [])
    {
        $bodyTmp = $this->body;
        foreach ($body as $k => $v) {
            $bodyTmp [$k] = $v;
        }
        $this->body = $bodyTmp;
        return $this;
    }

    /**
     * @param $uri
     * @return $this
     */
    public function setUrlRequest($uri)
    {
        $uri = trim($uri, '/');
        $this->uriRequest = $this->uriBase . '/' . $uri;
        return $this;
    }

    /**
     * Not support send file
     * @param $method
     * @return mixed|ResponseInterface
     * @throws GuzzleException
     */
    public function send($method)
    {
        if (!in_array(strtoupper($method), ['GET', 'POST', 'DELETE', 'HEAD', 'OPTIONS', 'PATCH', 'PUT'])) {
            throw new \Exception('Method invalid');
        }
        $request = new Request(strtoupper($method), $this->uriRequest, $this->headers, json_encode($this->body));
        $resp = $this->client->send($request);
        if (!$resp->getStatusCode() == 200) {
            throw new Exception($resp->getReasonPhrase());
        }

        $resp = $resp->getBody()->getContents();
        $resp = json_decode($resp, true);
        if (!isset($resp['success']) || $resp['success'] !== 1) {
            throw new Exception($resp);
        }
        return $resp;
    }
}
