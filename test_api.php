<?php
// File untuk test koneksi ke API
require_once 'config.php';

echo "<h2>Test Koneksi ke API</h2>";
echo "<p><strong>API BASE URL:</strong> " . API_BASE_URL . "</p>";
echo "<p><strong>Login Endpoint:</strong> " . API_LOGIN . "</p>";

// Test beberapa variasi URL
$testUrls = [
    API_LOGIN,
    API_BASE_URL . '/auth/login.php',
    str_replace('https://', 'http://', API_BASE_URL) . '/auth/login.php',
    str_replace('http://', 'https://', API_BASE_URL) . '/auth/login.php',
];

echo "<h3>Test 1: Cek Koneksi ke Berbagai URL</h3>";
echo "<p>Mencoba beberapa variasi URL untuk menemukan yang benar:</p>";

foreach ($testUrls as $index => $testUrl) {
    if ($testUrl === API_LOGIN && $index > 0) continue; // Skip duplicate
    
    echo "<h4>URL " . ($index + 1) . ": " . htmlspecialchars($testUrl) . "</h4>";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $testUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request untuk cek apakah URL ada

    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo "<p style='color: red;'><strong>✗ cURL Error:</strong> " . htmlspecialchars($error) . "</p>";
    } elseif ($httpCode == 200 || $httpCode == 405) {
        // 200 = OK, 405 = Method Not Allowed (tapi endpoint ada)
        echo "<p style='color: green;'><strong>✓ URL Valid! (HTTP " . $httpCode . ")</strong></p>";
    } elseif ($httpCode == 404) {
        echo "<p style='color: red;'><strong>✗ Not Found (404)</strong></p>";
    } else {
        echo "<p style='color: orange;'><strong>⚠ HTTP " . $httpCode . "</strong></p>";
    }
    echo "<hr>";
}

// Test dengan POST request ke URL yang benar
$testUrl = API_LOGIN;
echo "<h3>Test 2: Test POST Request ke Login API</h3>";
echo "<p>Menggunakan URL: <strong>" . htmlspecialchars($testUrl) . "</strong></p>";

// Test dengan POST request
echo "<h3>Test 2: Test POST Request ke Login API</h3>";
$testData = [
    'username' => 'admin',
    'password' => 'admin123'
];

$response = apiRequest(API_LOGIN, 'POST', $testData);

echo "<p><strong>Response dari apiRequest():</strong></p>";
echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
print_r($response);
echo "</pre>";

if ($response && isset($response['success'])) {
    if ($response['success']) {
        echo "<p style='color: green;'><strong>✓ API berfungsi dengan baik!</strong></p>";
    } else {
        echo "<p style='color: orange;'><strong>⚠ API merespons tapi ada error:</strong> " . htmlspecialchars($response['message'] ?? 'Unknown error') . "</p>";
    }
} else {
    echo "<p style='color: red;'><strong>✗ API tidak merespons dengan benar</strong></p>";
}
?>
