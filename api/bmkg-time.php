<?php
// bmkg-time.php
//header("Access-Control-Allow-Origin: *");

header('Content-Type: application/json');
header('Content-Type: application/json; charset=utf-8');

// Ambil HTML dari BMKG
$html = file_get_contents('https://ntp.bmkg.go.id/Jam.BMKG');

if ($html === FALSE) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal koneksi ke BMKG']);
    exit;
}

// Cari string waktu server di dalam HTML
// Pola regex ini mencari tanggal di dalam variabel 'servertimestring'
if (preg_match("/new Date\('(.*?)'\)/", $html, $matches) || preg_match("/: '(.*?)'/", $html, $matches)) {
    $serverTimeStr = $matches[1]; // Contoh: "2/12/2026 8:35:06 PM"
    
    // Ubah string waktu BMKG menjadi timestamp UTC
    // Penting: Kita asumsikan server BMKG me-return waktu dalam UTC atau waktu lokal server yang stabil
    $timestamp = strtotime($serverTimeStr);

    echo json_encode([
        'status' => 'success',
        'server_time_utc' => $timestamp * 1000 // Kirim dalam milidetik
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Format waktu BMKG berubah/tidak ditemukan']);
}
?>