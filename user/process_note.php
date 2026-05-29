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

// Check if smart note already exists
$stmt = $pdo->prepare('SELECT summary, key_points, flashcards FROM smart_notes WHERE note_id = ?');
$stmt->execute([$note_id]);
$existing = $stmt->fetch();

if ($existing) {
    echo json_encode([
        'success' => true, 
        'data' => [
            'summary' => $existing['summary'],
            'key_points' => json_decode($existing['key_points'], true),
            'flashcards' => json_decode($existing['flashcards'], true)
        ]
    ]);
    exit;
}

// Fallback: Using Title and Description as the text context since PDF parsing isn't available
$context_text = "Subject: $title\nDescription: $description\nPlease generate a summary, key points, and flashcards based on this topic.";

// Call Groq API
$api_key = defined('GROQ_API_KEY') ? GROQ_API_KEY : '';

if (empty($api_key)) {
    echo json_encode(['success' => false, 'error' => 'Groq API key is missing or not configured.']);
    exit;
}

$url = "https://api.groq.com/openai/v1/chat/completions";

$prompt = "You are an AI tutor. Analyze the following academic context: \"$context_text\". 
Return STRICT JSON ONLY, with NO markdown formatting, NO backticks, in this exact format:
{
  \"summary\": \"short simple summary of the topic\",
  \"key_points\": [\"point1\", \"point2\", \"point3\"],
  \"flashcards\": [
    {\"question\": \"...\", \"answer\": \"...\"}
  ]
}";

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
// SSL Verify disabled for local XAMPP compatibility
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

if (!$ai_data) {
    echo json_encode(['success' => false, 'error' => 'AI returned invalid JSON format.']);
    exit;
}

// Store in database
$insertStmt = $pdo->prepare('INSERT INTO smart_notes (note_id, summary, key_points, flashcards) VALUES (?, ?, ?, ?)');
$insertStmt->execute([
    $note_id,
    $ai_data['summary'],
    json_encode($ai_data['key_points']),
    json_encode($ai_data['flashcards'])
]);

echo json_encode([
    'success' => true,
    'data' => $ai_data
]);
?>
