<?php
// Konfigurasi API URL
// URL API yang sudah di-hosting

// Konfigurasi API URL
// URL API yang sudah di-hosting

// URL API yang benar - sesuaikan dengan domain cPanel Anda
define('API_BASE_URL', 'https://e-cuti-fst.my.id/api');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// API Endpoints
define('API_LOGIN', API_BASE_URL . '/auth/login.php');
define('API_LOGOUT', API_BASE_URL . '/auth/logout.php');
define('API_PROFILE', API_BASE_URL . '/profile/index.php');
define('API_PEGAWAI', API_BASE_URL . '/pegawai/index.php');
define('API_IZIN_CUTI', API_BASE_URL . '/izin-cuti/index.php');
define('API_IZIN_KETIDAKHADIRAN', API_BASE_URL . '/izin-ketidakhadiran/index.php');
define('API_JENIS_CUTI', API_BASE_URL . '/jenis-cuti/index.php');
define('API_JURUSAN', API_BASE_URL . '/jurusan/index.php');
define('API_PRODI', API_BASE_URL . '/prodi/index.php');

// Helper function untuk API calls
function apiRequest($url, $method = 'GET', $data = null, $token = null)
{
    if (!function_exists('curl_init')) {
        return [
            'success' => false,
            'message' => 'cURL extension tidak tersedia',
            'data' => null
        ];
    }

    $ch = curl_init();

    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    // Nonaktifkan SSL verification untuk sementara (aktifkan kembali jika SSL sudah benar)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    // Untuk production dengan SSL yang valid, aktifkan kembali:
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

    if ($data && ($method === 'POST' || $method === 'PUT')) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return [
            'success' => false,
            'message' => 'cURL Error: ' . $error . ' (URL: ' . $url . ')',
            'data' => null
        ];
    }

    if ($response === false) {
        return [
            'success' => false,
            'message' => 'Tidak dapat terhubung ke server API (URL: ' . $url . ')',
            'data' => null
        ];
    }

    // Trim response untuk menghilangkan whitespace
    $response = trim($response);

    // Cek jika response kosong
    if (empty($response)) {
        return [
            'success' => false,
            'message' => 'Response kosong dari server (HTTP ' . $httpCode . ')',
            'data' => null
        ];
    }

    // Cek jika response adalah HTML (biasanya error page)
    if (stripos($response, '<html') !== false || stripos($response, '<!DOCTYPE') !== false) {
        // Extract error message dari HTML jika memungkinkan
        $errorMsg = 'Server mengembalikan HTML bukan JSON';
        if (preg_match('/<title>(.*?)<\/title>/i', $response, $matches)) {
            $errorMsg .= ': ' . $matches[1];
        } elseif (preg_match('/<h1>(.*?)<\/h1>/i', $response, $matches)) {
            $errorMsg .= ': ' . $matches[1];
        }
        return [
            'success' => false,
            'message' => $errorMsg . ' (HTTP ' . $httpCode . ')',
            'data' => null
        ];
    }

    // Decode JSON
    $result = json_decode($response, true);

    if ($result === null && json_last_error() !== JSON_ERROR_NONE) {
        // Tampilkan preview response untuk debugging
        $preview = substr($response, 0, 200);
        if (strlen($response) > 200) {
            $preview .= '...';
        }

        return [
            'success' => false,
            'message' => 'Error parsing JSON: ' . json_last_error_msg() . ' (HTTP ' . $httpCode . '). Response: ' . htmlspecialchars($preview),
            'data' => null
        ];
    }

    return $result;
}

// Check if user is authenticated
function isAuthenticated()
{
    if (!isset($_SESSION['admin_token']) || !isset($_SESSION['admin_user'])) {
        return false;
    }

    $allowed_roles = [
        'admin', 
        'dosen', 'kajur', 'wdku', 'kaprodi', 'dosen_tugas', 
        'pegawai', 'dekan', 'wdak', 'wakil_rektor_2'
    ];

    return in_array($_SESSION['admin_user']['role'], $allowed_roles);
}

// Require authentication
function requireAuth()
{
    if (!isAuthenticated()) {
        header('Location: login.php');
        exit();
    }
}

// Get current user
function getCurrentUser()
{
    return $_SESSION['admin_user'] ?? null;
}

// Get auth token
function getAuthToken()
{
    return $_SESSION['admin_token'] ?? null;
}

// Format date
function formatDate($dateString)
{
    if (empty($dateString))
        return '-';
    return date('d F Y', strtotime($dateString));
}

// Format datetime
function formatDateTime($dateString)
{
    if (empty($dateString))
        return '-';
    return date('d F Y H:i', strtotime($dateString));
}

// Get status badge
function getStatusBadge($status)
{
    $badges = [
        'pending' => '<span class="badge bg-warning">Menunggu</span>',
        'approved_1' => '<span class="badge bg-info">Disetujui Tahap 1</span>',
        'approved' => '<span class="badge bg-success">Disetujui Final</span>',
        'rejected' => '<span class="badge bg-danger">Ditolak</span>'
    ];

    return $badges[$status] ?? '<span class="badge bg-secondary">' . $status . '</span>';
}
?>
