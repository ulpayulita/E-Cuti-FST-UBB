<?php
// Script untuk menemukan URL API yang benar
echo "<!DOCTYPE html><html><head><title>Find API URL</title>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} pre{background:#f5f5f5;padding:10px;border:1px solid #ddd;}</style>";
echo "</head><body>";
echo "<h1>🔍 Mencari URL API yang Benar</h1>";

// Daftar kemungkinan URL untuk dicoba
$baseUrls = [
    'https://fluttercuti.jokifigma.cloud/api',
    'http://fluttercuti.jokifigma.cloud/api',
    'https://fluttercuti.jokifigma.cloud',
    'http://fluttercuti.jokifigma.cloud',
];

$endpoints = [
    '/auth/login.php',
    '/api/auth/login.php',
    '/pengajuancuti/api/auth/login.php',
];

echo "<h2>Mencoba berbagai kombinasi URL...</h2>";

$found = false;
$workingUrls = [];

foreach ($baseUrls as $baseUrl) {
    foreach ($endpoints as $endpoint) {
        // Skip jika endpoint sudah mengandung base path
        if (strpos($endpoint, '/api') === 0 && strpos($baseUrl, '/api') !== false) {
            continue;
        }
        if (strpos($endpoint, '/pengajuancuti') === 0 && strpos($baseUrl, '/pengajuancuti') !== false) {
            continue;
        }
        
        $testUrl = rtrim($baseUrl, '/') . $endpoint;
        
        echo "<h3>Testing: <code>" . htmlspecialchars($testUrl) . "</code></h3>";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $testUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request
        
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            echo "<p class='error'>✗ Error: " . htmlspecialchars($error) . "</p>";
        } elseif ($httpCode == 200 || $httpCode == 405) {
            echo "<p class='success'><strong>✓ FOUND! HTTP " . $httpCode . "</strong></p>";
            $workingUrls[] = [
                'url' => $testUrl,
                'base' => $baseUrl,
                'endpoint' => $endpoint,
                'code' => $httpCode
            ];
            $found = true;
        } elseif ($httpCode == 404) {
            echo "<p class='error'>✗ Not Found (404)</p>";
        } else {
            echo "<p class='info'>⚠ HTTP " . $httpCode . "</p>";
        }
        echo "<hr>";
    }
}

if ($found) {
    echo "<h2 style='color:green;'>✅ URL yang Ditemukan:</h2>";
    foreach ($workingUrls as $working) {
        echo "<div style='background:#e8f5e9;padding:15px;margin:10px 0;border-left:4px solid green;'>";
        echo "<p><strong>Base URL:</strong> <code>" . htmlspecialchars($working['base']) . "</code></p>";
        echo "<p><strong>Endpoint Pattern:</strong> <code>" . htmlspecialchars($working['endpoint']) . "</code></p>";
        echo "<p><strong>Full URL:</strong> <code>" . htmlspecialchars($working['url']) . "</code></p>";
        echo "<p><strong>HTTP Code:</strong> " . $working['code'] . "</p>";
        echo "</div>";
    }
    
    // Test dengan POST request
    echo "<h2>Test POST Request ke URL yang Ditemukan:</h2>";
    $testUrl = $workingUrls[0]['url'];
    echo "<p>Testing POST ke: <code>" . htmlspecialchars($testUrl) . "</code></p>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $testUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['username' => 'test', 'password' => 'test']));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<p><strong>Response (HTTP " . $httpCode . "):</strong></p>";
    echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "</pre>";
    
    if (json_decode($response, true)) {
        echo "<p class='success'><strong>✓ API merespons dengan JSON yang valid!</strong></p>";
        echo "<h3>Konfigurasi untuk config.php:</h3>";
        echo "<pre style='background:#fff3cd;padding:15px;border:2px solid #ffc107;'>";
        echo "define('API_BASE_URL', '" . htmlspecialchars($workingUrls[0]['base']) . "');\n";
        echo "// Endpoint akan otomatis: API_BASE_URL . '" . htmlspecialchars($workingUrls[0]['endpoint']) . "'";
        echo "</pre>";
    }
} else {
    echo "<h2 style='color:red;'>❌ Tidak ada URL yang ditemukan</h2>";
    echo "<p><strong>Saran:</strong></p>";
    echo "<ul>";
    echo "<li>Pastikan API sudah di-upload ke hosting</li>";
    echo "<li>Cek struktur folder di cPanel File Manager</li>";
    echo "<li>Coba akses langsung di browser: <code>https://fluttercuti.jokifigma.cloud/api/index.php</code></li>";
    echo "<li>Atau: <code>https://fluttercuti.jokifigma.cloud/api/auth/login.php</code></li>";
    echo "<li>Periksa apakah ada .htaccess yang mempengaruhi routing</li>";
    echo "</ul>";
}

echo "</body></html>";
?>
