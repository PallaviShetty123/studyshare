<?php
require_once __DIR__ . '/../common/config.php';

$api_key = defined('GROQ_API_KEY') ? GROQ_API_KEY : '';

// Test with updated model
$url = "https://api.groq.com/openai/v1/chat/completions";

$payload = [
    'model' => 'llama-3.1-8b-instant',
    'messages' => [
        [
            'role' => 'user',
            'content' => 'Say hello in one sentence.'
        ]
    ],
    'temperature' => 0.3
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $api_key
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

echo "Testing with model: llama-3.1-8b-instant\n";
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code\n";

if ($http_code === 200) {
    $result = json_decode($response, true);
    $text = $result['choices'][0]['message']['content'] ?? 'No content';
    echo "SUCCESS! AI Response: $text\n";
} else {
    echo "FAILED! Response:\n$response\n";
}
