<?php

namespace Simplon\Frontend;

class Request
{
    /**
     * @param array $opt
     *
     * @return bool|string
     */
    protected static function request(array $opt)
    {
        $curl = curl_init();
        curl_setopt_array($curl, $opt);
        $response = curl_exec($curl);
        curl_close($curl);

        if ($response)
        {
            return (string)$response;
        }

        return false;
    }

    /**
     * @param $url
     * @param array $data
     *
     * @return bool|string
     */
    public static function get($url, array $data)
    {
        $opt = [
            CURLOPT_URL            => $url . '?' . http_build_query($data),
            CURLOPT_RETURNTRANSFER => 1
        ];

        return self::request($opt);
    }

    /**
     * @param $url
     * @param array $data
     *
     * @return bool|string
     */
    public static function post($url, array $data)
    {
        $opt = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST           => 1,
            CURLOPT_POSTFIELDS     => $data
        ];

        return self::request($opt);
    }

    /**
     * @param $url
     * @param $method
     * @param array $params
     * @param int $id
     *
     * @return array
     * @throws Exception
     */
    public static function jsonRpc($url, $method, array $params = [], $id = 1)
    {
        $opt = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST           => 1,
            CURLOPT_HTTPHEADER     => ['Content-type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode([
                'jsonrpc' => '2.0',
                'id'      => $id,
                'method'  => $method,
                'params'  => $params,
            ]),
        ];

        // request
        $response = self::request($opt);

        // decode json
        $decoded = json_decode($response, true);

        if ($decoded === null)
        {
            throw new Exception('JSONRPC request error: ' . $response);
        }

        return (array)$decoded;
    }
}