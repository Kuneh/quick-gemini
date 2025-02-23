<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Gemini API</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .explanation {
            white-space: pre-wrap;
        }

        textarea.custom-input,
        input.custom-input,
        select.custom-input {
            resize: none;
            border-width: 1px;
            line-height: normal;
        }

        textarea.custom-input {
            overflow-y: hidden;
            min-height: 38px;
            height: auto;
        }


        #loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            color: white;
            font-family: monospace;
            font-size: 1.2em;
            text-align: center;
        }

        #loading-text {
            /* Style for loading text */

        }

        #error-text {
            /* Style for error text in overlay */
            color: red;
            font-weight: bold;
            font-size: 1.5em;
        }


        .dropdown {
            position: relative;
        }

        .dropdown-button {
            @apply bg-gray-700 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full flex items-center cursor-pointer border border-gray-600;
            padding-right: 1.5rem;
            /* Reduced padding */
        }

        .dropdown-button span {
            flex-grow: 1;
            text-align: left;
        }

        .dropdown-button:after {
            content: "";
            display: inline-block;
            /* Changed to inline-block */
            width: 0.6em;
            height: 0.6em;
            border-bottom: 0.15em solid white;
            border-left: 0.15em solid white;
            transform: rotate(-45deg);
            transition: transform 0.3s ease-in-out;
            margin-left: 0.5em;
            /* Added margin to separate from text */
            vertical-align: middle;
            /* Align arrow vertically with text */
            position: relative;
            /* Needed for vertical-align to work in some layouts */
            top: -0.1em;
            /* Slightly move arrow up */
        }


        .dropdown-button.open:after {
            transform: rotate(135deg);
            top: 0.1em;
            /* Slightly move arrow down when open, adjust as needed */
        }

        .dropdown-content {
            background-color: #2d3748;
            border: 1px solid #4a5568;
            border-top: none;
            border-radius: 0 0 0.375rem 0.375rem;
            overflow: hidden;
            max-height: 0;
            transition: max-height 0.3s ease-in-out;
            z-index: 10;
            margin-top: 2px;
            margin-left: -1px;
            margin-right: -1px;
        }

        .dropdown-content.open {
            max-height: 500px;
        }

        .dropdown-inner {
            padding: 0.75rem;
        }

        .dropdown-inner textarea.custom-input {
            padding: 0.5rem;
        }
    </style>
</head>

<body class="bg-gray-900 text-white p-6">

    <div class="max-w-3xl mx-auto bg-gray-800 shadow-md rounded-md p-6">

        <h1 class="text-2xl font-semibold mb-4 text-gray-100">Quick Gemini API</h1>

        <?php echo form_open(base_url()); ?>

        <div class="mb-4">
            <label for="api_key" class="block text-gray-200 text-sm font-bold mb-2">API Key:</label>
            <input type="text" id="api_key" name="api_key" value="<?php echo isset($api_key) ? html_escape($api_key) : ''; ?>" class="custom-input shadow appearance-none border rounded w-full py-2 px-3 text-gray-200 leading-tight focus:outline-none focus:shadow-outline bg-gray-700 text-white border-gray-600" placeholder="Enter your API Key">
        </div>

        <div class="mb-4">
            <label for="prompt" class="block text-gray-200 text-sm font-bold mb-2">Enter your prompt:</label>
            <textarea id="prompt" name="prompt" rows="1" class="custom-input shadow appearance-none border rounded w-full py-2 px-3 text-gray-200 leading-tight focus:outline-none focus:shadow-outline bg-gray-700 text-white border-gray-600" placeholder="Ask me anything!"><?php echo isset($prompt) ? html_escape($prompt) : ''; ?></textarea>
        </div>

        <div class="mb-4">
            <label for="model_name" class="block text-gray-200 text-sm font-bold mb-2">Gemini Model:</label>
            <select id="model_name" name="model_name" onchange="toggleSystemInstruction()" class="custom-input shadow appearance-none border rounded w-full py-2 px-3 text-gray-200 leading-tight focus:outline-none focus:shadow-outline bg-gray-700 text-white border-gray-600">
                <option value="gemini-2.0-flash" <?php echo (isset($model_name) && $model_name == 'gemini-2.0-flash') ? 'selected' : ''; ?>>Gemini 2.0 Flash</option>
                <option value="gemini-2.0-flash-lite-preview-02-05" <?php echo (isset($model_name) && $model_name == 'gemini-2.0-flash-lite-preview-02-05') ? 'selected' : ''; ?>>Gemini 2.0 Flash-Lite Preview 02-05</option>
                <option value="gemini-2.0-pro-exp-02-05" <?php echo (isset($model_name) && $model_name == 'gemini-2.0-pro-exp-02-05') ? 'selected' : ''; ?>>Gemini 2.0 Pro Experimental 02-05</option>
                <option value="gemini-2.0-flash-thinking-exp-01-21" <?php echo (isset($model_name) && $model_name == 'gemini-2.0-flash-thinking-exp-01-21') ? 'selected' : ''; ?>>Gemini 2.0 Flash Thinking Experimental 01-21</option>
                <option value="gemini-1.5-flash" <?php echo (isset($model_name) && $model_name == 'gemini-1.5-flash') ? 'selected' : ''; ?>>Gemini 1.5 Flash (Legacy)</option>
                <option value="gemini-1.0-pro" <?php echo (isset($model_name) && $model_name == 'gemini-1.0-pro') ? 'selected' : ''; ?>>Gemini 1.0 Pro (Legacy)</option>
                <option value="gemini-pro" <?php echo (isset($model_name) && $model_name == 'gemini-pro') ? 'selected' : ''; ?>>Gemini Pro (Legacy)</option>
            </select>
        </div>

        <div class="mb-4">
            <label for="temperature" class="block text-gray-200 text-sm font-bold mb-2">Creativity (0-2):</label>
            <input type="number" id="temperature" name="temperature" min="0" max="2" step="0.1" value="<?php echo isset($temperature) ? html_escape($temperature) : '0.5'; ?>" class="custom-input shadow appearance-none border rounded w-20 py-2 px-3 text-gray-200 leading-tight focus:outline-none focus:shadow-outline bg-gray-700 text-white border-gray-600">
        </div>

        <div id="system_instruction_dropdown" class="mb-4 dropdown">
            <button type="button" class="dropdown-button" onclick="toggleDropdown('system_instruction_dropdown')">
                <span>System Instruction</span>
            </button>
            <div class="dropdown-content" id="system_instruction_content">
                <div class="dropdown-inner">
                    <label for="system_instruction" class="block text-gray-200 text-sm font-bold mb-2 sr-only">System Instruction:</label>
                    <textarea id="system_instruction" name="system_instruction" rows="1" oninput="restrictNonEnglish(this)" class="custom-input shadow appearance-none border rounded w-full py-2 px-3 text-gray-200 leading-tight focus:outline-none focus:shadow-outline bg-gray-700 text-white border-gray-600" placeholder="You are a helpful AI assistant."><?php echo isset($system_instruction) ? html_escape($system_instruction) : 'You are a helpful AI assistant.'; ?></textarea>
                </div>
            </div>
        </div>


        <div id="safety_settings_dropdown" class="mb-4 dropdown">
            <button type="button" class="dropdown-button" onclick="toggleDropdown('safety_settings_dropdown')">
                <span>Safety Settings</span>
            </button>
            <div class="dropdown-content" id="safety_settings_content">
                <div class="dropdown-inner">
                    <h2 class="text-lg font-semibold mt-2 text-gray-100">Safety Settings:</h2>
                    <?php
                    $threshold_map = [
                        'BLOCK_NONE' => ['value' => 0, 'label' => 'Allow All'],
                        'BLOCK_ONLY_LOW' => ['value' => 1, 'label' => 'Block Low+'],
                        'BLOCK_MEDIUM_AND_ABOVE' => ['value' => 2, 'label' => 'Block Medium+'],
                        'BLOCK_ONLY_HIGH' => ['value' => 3, 'label' => 'Block High Only']
                    ];

                    $category_names = [
                        'HARM_CATEGORY_HARASSMENT' => 'Harassment',
                        'HARM_CATEGORY_HATE_SPEECH' => 'Hate Speech',
                        'HARM_CATEGORY_SEXUALLY_EXPLICIT' => 'Sexually Explicit',
                        'HARM_CATEGORY_DANGEROUS_CONTENT' => 'Dangerous Content'
                    ];

                    foreach ($safety_settings as $category => $setting):
                        $current_value = $threshold_map[$setting]['value'];
                    ?>
                        <div class="mb-4">
                            <div class="flex justify-between mb-1">
                                <span class="text-gray-200 text-sm">
                                    <?php echo $category_names[$category] ?? str_replace(['HARM_CATEGORY_', '_'], ['', ' '], $category); ?>
                                </span>
                                <span class="text-gray-400 text-sm" id="label-<?php echo $category; ?>">
                                    <?php echo $threshold_map[$setting]['label']; ?>
                                </span>
                            </div>
                            <input
                                type="range"
                                min="0"
                                max="3"
                                value="<?php echo $current_value; ?>"
                                class="w-full h-2 bg-gray-600 rounded-lg appearance-none cursor-pointer"
                                oninput="updateSafetySetting('<?php echo $category; ?>', this.value)">
                            <input
                                type="hidden"
                                name="safety_settings[<?php echo $category; ?>]"
                                id="input-<?php echo $category; ?>"
                                value="<?php echo $setting; ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>


        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" onclick="return showLoading()">Submit</button>
        <?php echo form_close(); ?>

        <?php if (isset($error) && $error !== "API Key is required."): ?> <!-- Only show inline error if it's NOT the API key error -->
            <div class="text-red-500 text-sm mt-2"><?php echo html_escape($error); ?></div>
        <?php endif; ?>

        <?php if (isset($ai_explanation)): ?>
            <h2 class="text-lg font-semibold mt-6 text-gray-100">Response:</h2>
            <div class="explanation bg-gray-700 border border-gray-600 rounded-md p-4 mt-2 text-gray-200"><?php echo html_escape($ai_explanation); ?></div>
        <?php endif; ?>

    </div>

    <div id="loading-overlay">
        <div id="loading-content">
            <div id="loading-text"></div>
            <div id="error-text"></div>
        </div>
    </div>


</body>
<script>
    function restrictNonEnglish(textarea) {
        textarea.value = textarea.value.replace(/[^a-zA-Z\s.,?!'"-]/g, '');
    }

    function toggleSystemInstruction() {
        var modelSelect = document.getElementById("model_name");
        var systemInstructionDropdown = document.getElementById("system_instruction_dropdown");

        if (modelSelect.value === "gemini-2.0-flash-thinking-exp-01-21") {
            systemInstructionDropdown.classList.add("hidden");
        } else {
            systemInstructionDropdown.classList.remove("hidden");
        }
    }

    const thresholdValues = {
        0: {
            setting: 'BLOCK_NONE',
            label: 'Allow All'
        },
        1: {
            setting: 'BLOCK_ONLY_LOW',
            label: 'Block Low+'
        },
        2: {
            setting: 'BLOCK_MEDIUM_AND_ABOVE',
            label: 'Block Medium+'
        },
        3: {
            setting: 'BLOCK_ONLY_HIGH',
            label: 'Block High Only'
        }
    };

    function updateSafetySetting(category, value) {
        const selected = thresholdValues[value];
        document.getElementById(`input-${category}`).value = selected.setting;
        document.getElementById(`label-${category}`).textContent = selected.label;
    }

    window.onload = toggleSystemInstruction;

    function showLoading() {
        var apiKey = document.getElementById("api_key").value;
        var loadingOverlay = document.getElementById("loading-overlay");
        var loadingTextDiv = document.getElementById("loading-text");
        var errorTextDiv = document.getElementById("error-text");
        var loadingContentDiv = document.getElementById("loading-content");


        if (apiKey === "") {
            loadingOverlay.style.display = "flex";
            loadingTextDiv.textContent = ""; // Clear loading text
            errorTextDiv.textContent = "API Key is required. Please provide your API key."; // Set error text

            setTimeout(function() {
                errorTextDiv.textContent = ""; // Clear error message after 2.5 seconds
                loadingOverlay.style.display = "none"; // Hide the overlay
            }, 2500); // 2500 milliseconds = 2.5 seconds

            return false; // Prevent form submission and further loading messages
        } else {
            loadingOverlay.style.display = "flex";
            errorTextDiv.textContent = ""; // Clear error text
            loadingTextDiv.textContent = "Initializing Gemini..."; // Set initial loading text


            const phases = [
                "Initializing Gemini...",
                "Compiling prompt...",
                "Analyzing safety settings...",
                "Querying model...",
                "Generating response..."
            ];
            let phaseIndex = 0;

            const intervalId = setInterval(() => {
                loadingTextDiv.textContent = phases[phaseIndex];
                phaseIndex = (phaseIndex + 1) % phases.length;
            }, 1500);
            return true; // Allow form submission
        }
    }


    document.querySelectorAll('textarea.custom-input').forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    });

    function toggleDropdown(dropdownId) {
        const dropdownButton = document.querySelector(`#${dropdownId} .dropdown-button`);
        const dropdownContent = document.querySelector(`#${dropdownId} .dropdown-content`);

        if (dropdownButton && dropdownContent) {
            dropdownButton.classList.toggle('open');
            dropdownContent.classList.toggle('open');
            if (dropdownContent.classList.contains('open')) {
                dropdownContent.style.marginTop = '2px';
            } else {
                dropdownContent.style.marginTop = '2px';
            }
        }
    }
</script>

</html>