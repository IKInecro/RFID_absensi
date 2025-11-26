<?php
// pages/tester_clear.php
// Clear data untuk tester mode (test_data.json) — improved: LOCK_EX, better errors, returns JSON with proper code
header('Content-Type: application/json; charset=utf-8');
try {
    $testFile = __DIR__ . '/../test_data.json';

    // only allow POST for safety
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed. Gunakan POST.']);
        exit;
    }

    // ensure directory exists
    $dir = dirname($testFile);
    if (!is_dir($dir)) {
        if (!@mkdir($dir, 0755, true)) {
            throw new Exception('Folder pages/ tidak tersedia dan gagal dibuat. Periksa permission.');
        }
    }

    // if file not exists create an empty array file
    if (!file_exists($testFile)) {
        if (@file_put_contents($testFile, json_encode([], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX) === false) {
            throw new Exception('Gagal membuat file test_data.json. Periksa permission folder pages/.');
        }
    }

    // check writable
    if (!is_writable($testFile)) {
        // try to chmod, best-effort
        @chmod($testFile, 0666);
        if (!is_writable($testFile)) {
            throw new Exception('File test_data.json tidak dapat ditulisi. Periksa permission folder pages/.');
        }
    }

    // atomically overwrite with empty array
    $written = @file_put_contents($testFile, json_encode([], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
    if ($written === false) {
        throw new Exception('Gagal menulis file test_data.json. Periksa permission server.');
    }

    // success
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Data tester berhasil dibersihkan.']);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
?>