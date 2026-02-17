<?php
// Izinkan akses dari domain manapun (Solusi CORS)
//header("Access-Control-Allow-Origin: *");
//header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
//header("Access-Control-Allow-Headers: Content-Type, Authorization");

header('Content-Type: application/json');
header('Content-Type: application/json; charset=utf-8');

// Pastikan file .json ada di direktori yang sama
if (file_exists('kodewilayah_tree.json')) {
    echo file_get_contents('kodewilayah_tree.json');
} else {
    echo json_encode(["error" => "File tidak ditemukan"]);
}
?>