<?php
// Load environment variables
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (!empty($key) && !empty($value)) {
            putenv("$key=$value");
        }
    }
}

// Handle AJAX chat requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    header('Content-Type: application/json');
    
    $userMessage = trim($_POST['message']);
    
    if (empty($userMessage)) {
        echo json_encode(['error' => 'Message cannot be empty']);
        exit;
    }
    
    // Get API key from environment variable
    $apiKey = getenv('GROQ_API_KEY');
    
    if (!$apiKey) {
        echo json_encode(['error' => 'API key not configured. Please check GROQ_SETUP.md']);
        exit;
    }
    
    $ch = curl_init();
    
    // Allow explicit model configuration via .env if available
    $configuredModel = getenv('GROQ_MODEL');
    $models = $configuredModel
        ? [$configuredModel]
        : ['llama-3.1-8b-instant', 'llama-3.3-70b-versatile', 'openai/gpt-oss-20b', 'openai/gpt-oss-120b'];

    $response = null;
    $lastError = null;
    $successModel = null;
    
    foreach ($models as $model) {
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://api.groq.com/openai/v1/chat/completions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode([
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful assistant for Reread, a secondhand book marketplace. Help users with book recommendations, platform questions, and general book-related queries. Be friendly, concise, and helpful. Keep responses brief and conversational.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $userMessage
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 500,
            ]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($httpCode === 200) {
            $successModel = $model;
            break;
        }
        
        $lastError = $response;
    }
    
    curl_close($ch);
    
    if ($successModel === null) {
        error_log("Groq API Error - No working model found. Last error: " . $lastError);
        echo json_encode(['error' => 'Unable to connect to AI service. Check GROQ_SETUP.md for help.']);
        exit;
    }
    
    $data = json_decode($response, true);
    
    if (!$data) {
        error_log("Groq API Invalid JSON Response: " . $response);
        echo json_encode(['error' => 'Invalid response format']);
        exit;
    }
    
    if (isset($data['choices'][0]['message']['content'])) {
        echo json_encode([
            'success' => true,
            'message' => $data['choices'][0]['message']['content']
        ]);
    } else {
        error_log("Groq API No message content in response: " . json_encode($data));
        echo json_encode(['error' => 'Failed to generate response']);
    }
    
    exit;
}
?>
