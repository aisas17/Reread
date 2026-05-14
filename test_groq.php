<?php
// Test Groq API Connection
header('Content-Type: application/json');

// Load environment variables
$envFile = __DIR__ . '/.env';
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

$apiKey = getenv('GROQ_API_KEY');

if (!$apiKey) {
    die(json_encode([
        'error' => 'API key not found',
        'env_file_exists' => file_exists($envFile),
        'env_file_path' => $envFile
    ]));
}

// Test message
$testMessage = "Hello, what is a good book recommendation?";

$ch = curl_init();

// Try multiple supported models
$models = ['llama-3.1-8b-instant', 'llama-3.3-70b-versatile', 'openai/gpt-oss-20b', 'openai/gpt-oss-120b'];
$response = null;
$httpCode = 400;
$successModel = null;

foreach ($models as $model) {
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.groq.com/openai/v1/chat/completions',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode([
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful assistant for a book marketplace.'
                ],
                [
                    'role' => 'user',
                    'content' => $testMessage
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 300,
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
}

$curlError = curl_error($ch);
curl_close($ch);

echo json_encode([
    'http_code' => $httpCode,
    'curl_error' => $curlError,
    'api_key_set' => !empty($apiKey),
    'api_key_length' => strlen($apiKey),
    'working_model' => $successModel,
    'response' => $response ? json_decode($response, true) : null,
    'raw_response' => $response
]);
?>
