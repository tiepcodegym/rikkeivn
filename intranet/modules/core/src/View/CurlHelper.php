<?php
namespace Rikkei\Core\View;

class CurlHelper {

    /**
     * @param $url
     * @param $data
     * @param $header
     * @return bool|string
     */
    public static function httpPost($url, $data, $header = [])
    {
        $curl = curl_init();
        $header = array_merge([
            "Content-Type: application/x-www-form-urlencoded",
            "Accept: application/vnd.api+json"
        ], $header);
        $query = http_build_query($data);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $query,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => $header,
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public static function httpPut($url, $params, $header = [])
    {
        $curl = curl_init();
        $params = json_encode($params);
        $header = array_merge([
            "Content-Type: application/json",
            'Content-Length: ' . strlen($params)
        ], $header);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => $header,
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    /**
     * @param $url
     * @param $data
     * @param $header
     * @return bool|string
     */
    public static function httpGet($url, $data, $header)
    {
        $query = http_build_query($data);
        $ch = curl_init($url . '?' . $query);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); // Inject the token into the header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

}