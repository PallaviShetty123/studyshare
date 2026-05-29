<?php
require_once __DIR__ . '/common/config.php';
$key = defined('GROQ_API_KEY') ? GROQ_API_KEY : 'MISSING';
echo "Key parsed: [" . $key . "]\n";
