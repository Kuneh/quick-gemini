<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Gemini
{

    protected $ci;
    protected $api_key_config; // rename to avoid confusion
    protected $api_endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/';
    protected $default_model = 'gemini-2.0-flash';

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->library('rest_connect');
        $this->api_key_config = $this->ci->config->item('gemini_api_key'); // rename to api_key_config
    }

    public function generate_content($api_key, $prompt, $temperature = 0.5, $system_instruction = "", $model_name = null, $safetySettings = null) // add api_key as first parameter
    {
        if ($model_name === null) {
            $model_name = $this->default_model;
        }

        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => $temperature
            ]
        ];
        // Implement your safetySettings in your code for error handling.
        $safetySettingsForAPI = [];
        if (is_array($safetySettings)) {
            foreach ($safetySettings as $category => $threshold) {
                // Implement is_valid https://ai.google.dev/static/reference/rest/v1beta/GenerateContentRequest#SafetySetting
                if (strpos($category, 'HARM_CATEGORY_') !== false && strpos($threshold, 'BLOCK_') !== false) {
                    $safetySettingsForAPI[] = [
                        "category" => $category,
                        "threshold" => $threshold
                    ];
                }
            }
        }

        if (!empty($safetySettingsForAPI)) {
            $data['safetySettings'] = $safetySettingsForAPI;
        }
        // Conditionally add the tools parameter if there is a system instruction
        if (!empty($system_instruction)) {
            $data['tools'] = [
                [
                    "function_declarations" => [
                        [
                            "description" => $system_instruction,
                            "name" => "generate_response",
                            "parameters" => [
                                "type" => "OBJECT",
                                "properties" => [
                                    "response" => [
                                        "type" => "STRING",
                                        "description" => "AI Response"
                                    ]
                                ],
                                "required" => [
                                    "response"
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        } else {
            // Remove the tools parameter completely if there is no system instruction
            unset($data['tools']);
        }
        $api_url = $this->api_endpoint . $model_name . ':generateContent?key=' . urlencode($api_key); // use passed $api_key here
        $headers = ['Content-Type: application/json'];
        $body = json_encode($data);

        try {
            $response = $this->ci->rest_connect->http_request_post($api_url, $headers, $body);
            return ['response' => $response[0], 'http_code' => $response[1]];
        } catch (Exception $e) {
            log_message('error', 'Gemini API Error: ' . $e->getMessage());
            return ['response' => ['error' => 'API request failed: ' . $e->getMessage()], 'http_code' => 500];
        }
    }
}
