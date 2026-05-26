<?php
echo "cURL enabled: " . (function_exists('curl_init') ? 'YES' : 'NO') . PHP_EOL;
echo "OpenSSL: " . (extension_loaded('openssl') ? 'YES' : 'NO') . PHP_EOL;

// Check DNS resolution
$ip = gethostbyname('oauth2.googleapis.com');
echo "DNS resolve oauth2.googleapis.com => " . $ip . PHP_EOL;

// Check php.ini curl cainfo
echo "curl.cainfo: " . ini_get('curl.cainfo') . PHP_EOL;
echo "openssl.cafile: " . ini_get('openssl.cafile') . PHP_EOL;

// Test actual connection
$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
$res = curl_exec($ch);
$err = curl_error($ch);
$errno = curl_errno($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $code . PHP_EOL;
echo "cURL errno: " . $errno . PHP_EOL;
echo "cURL error: " . $err . PHP_EOL;
echo "Response: " . substr($res, 0, 200) . PHP_EOL;
