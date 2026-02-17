<?php
/**
 * BMKG XML/RSS Proxy
 * Upload file ini ke hosting Anda (misal: public_html/api/bmkg_proxy.php)
 */

// 1. Mengizinkan akses dari domain website utama Anda (CORS)
// Jika script ini beda domain dengan UI HTML-nya, biarkan '*'. 
// Untuk production, sangat disarankan ganti '*' dengan domain UI Anda, misal: 'https://ternate.bmkg.go.id'
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");

// Tangani request OPTIONS (Preflight) dari browser
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 2. Mendapatkan parameter URL target yang dikirim dari Javascript
$url = isset($_GET['url']) ? $_GET['url'] : '';

// Validasi jika URL kosong
if (empty($url)) {
    http_response_code(400);
    die("Error: Parameter 'url' tidak ditemukan.");
}

// 3. KEAMANAN (Mencegah Open Proxy / SSRF)
// Pastikan proxy ini HANYA diizinkan mengambil data dari domain resmi BMKG
$parsed_url = parse_url($url);

// Pola Regex:
// (?:^|\.) -> Cocokkan awal string ATAU tanda titik (untuk subdomain)
// bmkg\.go\.id -> Domain target (titik di-escape dengan \)
// $ -> Akhir string
$allowed_pattern = '/(?:^|\.)bmkg\.go\.id$/';

if (!isset($parsed_url['host']) || !preg_match($allowed_pattern, $parsed_url['host'])) {
    http_response_code(403);
    die("Error: Akses ditolak. Proxy ini hanya dikhususkan untuk domain dan subdomain BMKG.");
}

// Lanjut ke proses selanjutnya...

// 4. Mengambil data menggunakan cURL (lebih stabil daripada file_get_contents)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
// Beberapa server menolak request tanpa User-Agent
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; BMKG-Stameteo-Proxy/1.0)'); 
curl_setopt($ch, CURLOPT_TIMEOUT, 15); // Timeout 15 detik

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$curl_error = curl_error($ch);
curl_close($ch);

// 5. Meneruskan Header Content-Type asli dari BMKG ke browser
if ($content_type) {
    header("Content-Type: " . $content_type);
} else {
    // Default fallback untuk XML
    header("Content-Type: text/xml; charset=utf-8"); 
}

// 6. Output Response
if ($http_code >= 200 && $http_code < 300 && $response !== false) {
    echo $response;
} else {
    http_response_code($http_code ?: 500);
    echo "Error: Gagal mengambil data dari BMKG. HTTP Code: " . $http_code . " | cURL Error: " . $curl_error;
}
?>