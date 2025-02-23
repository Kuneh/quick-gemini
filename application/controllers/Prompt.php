<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Prompt extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('gemini');
        $this->load->helper('form');
    }

    public function index()
    {
        $data = [];

        // Default values
        $data['api_key'] = ''; // Add default for api_key
        $data['temperature'] = 0.5;
        $data['system_instruction'] = "";
        $data['model_name'] = 'gemini-2.0-flash';
        $data['safety_settings'] = [  // Default safety settings
            'HARM_CATEGORY_HARASSMENT' => 'BLOCK_MEDIUM_AND_ABOVE',
            'HARM_CATEGORY_HATE_SPEECH' => 'BLOCK_MEDIUM_AND_ABOVE',
            'HARM_CATEGORY_SEXUALLY_EXPLICIT' => 'BLOCK_MEDIUM_AND_ABOVE',
            'HARM_CATEGORY_DANGEROUS_CONTENT' => 'BLOCK_ONLY_HIGH',
        ];

        if ($this->input->post()) {
            $api_key = $this->input->post('api_key'); // Get api_key from post
            $prompt = $this->input->post('prompt');
            $temperature = $this->input->post('temperature');
            $system_instruction = $this->input->post('system_instruction');
            $model_name = $this->input->post('model_name');

            // Validate API Key (basic check, you might need more robust validation)
            if (empty($api_key)) {
                $data['error'] = "API Key is required."; // Specific error for empty API key
            }


            // Validate temperature
            if (!isset($data['error']) && !is_numeric($temperature)) {
                $data['error'] = "Temperature must be a number.";
            } else if (!isset($data['error'])) {
                $temperature = floatval($temperature);
                if ($temperature < 0 || $temperature > 2) { // Temperature range was 0-1 before, corrected to 0-2 as in view
                    $data['error'] = "Temperature must be between 0 and 2.";
                }
            }

            // Model name validation (important!)
            $allowed_models = [
                'gemini-2.0-flash',
                'gemini-2.0-flash-lite-preview-02-05',
                'gemini-2.0-pro-exp-02-05',
                'gemini-2.0-flash-thinking-exp-01-21',
                'gemini-1.5-flash', // Keep older models in case they're still supported
                'gemini-1.0-pro',
                'gemini-pro'
            ];
            if (!isset($data['error']) && !in_array($model_name, $allowed_models)) {
                $data['error'] = "Invalid model name.";
                $model_name = 'gemini-2.0-flash';
            }

            // Remove non-English characters from system instruction
            $system_instruction = preg_replace('/[^a-zA-Z\s.,?!\'"\\-]/u', '', $system_instruction);
            if ($system_instruction === "") {
                $data['system_instruction'] = "";
            }

            // Get safety settings
            $safetySettings = $this->input->post('safety_settings');
            if (is_array($safetySettings)) {
                $data['safety_settings'] = $safetySettings;
            }

            if (!isset($data['error'])) {
                // Pass api_key to the generate_content function
                $api_result = $this->gemini->generate_content($api_key, $prompt, $temperature, $system_instruction, $model_name, $data['safety_settings']);

                $http_code = $api_result['http_code'];
                $response = $api_result['response'];

                $data['api_response'] = $response; // Store the full response
                $data['http_code'] = $http_code;     // Store the HTTP code

                if ($http_code >= 200 && $http_code < 300) {
                    // Success!
                    // ... your existing logic to extract the AI explanation ...
                    if (isset($response['candidates'][0]['content']['parts'][0]['functionCall']['args']['response'])) {
                        // Extract from functionCall
                        $data['ai_explanation'] = $response['candidates'][0]['content']['parts'][0]['functionCall']['args']['response'];
                    } else {
                        // Extract from standard text response
                        if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
                            $data['ai_explanation'] = $response['candidates'][0]['content']['parts'][0]['text'];
                        } else {
                            $data['error'] = "Unexpected API response format. Check the response structure.";
                        }
                    }
                } else {
                    // Handle Error
                    $data['error'] = "API request failed with HTTP code: " . $http_code;
                }
            }
            $data['api_key'] = $api_key; // Pass api_key back to view
            $data['prompt'] = $prompt;
            $data['temperature'] = $temperature;
            $data['system_instruction'] = $system_instruction;
            $data['model_name'] = $model_name;
        }


        $this->load->view('gemini_view', $data);
    }
}
