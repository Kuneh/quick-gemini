<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Rest_Connect
{
    private function curl_request($url, $method, $header, $body)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

        // Log request details
        // log_message('debug', "CURL Request: Method - {$method}, URL - {$url}");
        // log_message('debug', "CURL Request Headers: " . print_r($header, true));
        // log_message('debug', "CURL Request Body: " . $body);

        $output = curl_exec($ch); // Execute, but don't decode yet
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Log response details
        // log_message('debug', "CURL Response: HTTP Code - {$http_code}");
        // log_message('debug', "CURL Response Body: {$output}");

        curl_close($ch);

        return [$output, $http_code]; // Return raw output and HTTP code
    }

    public function http_request_post($url, $header, $body)
    {
        list($output, $http_code) = $this->curl_request($url, 'POST', $header, $body);
        $output = json_decode($output, true); // Now decode JSON
        return [$output, $http_code];
    }

    public function http_request_put($url, $header, $body)
    {
        list($output, $http_code) = $this->curl_request($url, 'PUT', $header, $body);
        $output = json_decode($output, true);
        return [$output, $http_code];
    }

    public function http_request_get($url, $header, $body)
    {
        list($output, $http_code) = $this->curl_request($url, 'GET', $header, $body);
        $output = json_decode($output, true);
        return [$output, $http_code];
    }

    public function http_request_delete($url, $header, $body)
    {
        list($output, $http_code) = $this->curl_request($url, 'DELETE', $header, $body);
        $output = json_decode($output, true);
        return $output;
    }
}
