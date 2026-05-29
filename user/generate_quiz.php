<?php
require_once __DIR__ . '/../common/auth_user.php';
require_once __DIR__ . '/../common/db.php';

header('Content-Type: application/json');

$student = getCurrentStudent();
$pdo = db();

$data = json_decode(file_get_contents('php://input'), true);
$note_id = intval($data['note_id'] ?? 0);
$title = $data['title'] ?? '';
$description = $data['description'] ?? '';

if (!$note_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid note ID']);
    exit;
}

// Check if quiz already exists
$stmt = $pdo->prepare('SELECT questions FROM quiz_sets WHERE note_id = ?');
$stmt->execute([$note_id]);
$existing = $stmt->fetch();

if ($existing) {
    echo json_encode([
        'success' => true, 
        'data' => json_decode($existing['questions'], true)
    ]);
    exit;
}

// Fallback: Using Title and Description as text context
$context_text = "Subject: $title\nDescription: $description\nPlease generate exactly 5 multiple-choice questions based on this topic.";

// Call Groq API
$api_key = defined('GROQ_API_KEY') ? GROQ_API_KEY : '';

if (empty($api_key)) {
    echo json_encode(['success' => false, 'error' => 'Groq API key is missing or not configured.']);
    exit;
}

$url = "https://api.groq.com/openai/v1/chat/completions";

$prompt = "You are an AI teacher. Based on the following context: \"$context_text\".
Return STRICT JSON ONLY, with NO markdown formatting, NO backticks. Format exactly as this array:
[
  {
    \"question\": \"Question text here?\",
    \"options\": [\"Option A\", \"Option B\", \"Option C\", \"Option D\"],
    \"answer\": \"Option A\"
  }
]";

$payload = [
    'model' => 'llama-3.1-8b-instant',
    'messages' => [
        [
            'role' => 'user',
            'content' => $prompt
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

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_err = curl_error($ch);
curl_close($ch);

if ($http_code !== 200) {
    $errorMsg = "AI Service unavailable.";
    if ($http_code === 401) {
        $errorMsg = "Authentication failed. Invalid API key.";
    } elseif ($http_code === 429) {
        $errorMsg = "Rate limit exceeded. Please try again later.";
    }
    
    echo json_encode([
        'success' => false, 
        'error' => "$errorMsg HTTP Code: $http_code. cURL Error: $curl_err. Response: $response"
    ]);
    exit;
}

$result = json_decode($response, true);
$ai_text = $result['choices'][0]['message']['content'] ?? '';

// Clean up markdown block if the AI ignored instructions
$ai_text = preg_replace('/```json\s*/', '', $ai_text);
$ai_text = preg_replace('/```\s*/', '', $ai_text);
$ai_text = trim($ai_text);

$ai_data = json_decode($ai_text, true);

if (!$ai_data || !is_array($ai_data)) {
    echo json_encode(['success' => false, 'error' => 'AI returned invalid JSON format.']);
    exit;
}

// Store in database
$insertStmt = $pdo->prepare('INSERT INTO quiz_sets (note_id, questions) VALUES (?, ?)');
$insertStmt->execute([
    $note_id,
    json_encode($ai_data)
]);

echo json_encode([
    'success' => true,
    'data' => $ai_data
]);
?>
