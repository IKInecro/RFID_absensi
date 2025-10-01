<?php
include 'db.php';
date_default_timezone_set('Asia/Jakarta');

// Ambil filter dari query string
$from   = isset($_GET['from']) ? $conn->real_escape_string($_GET['from']) : '';
$to     = isset($_GET['to'])   ? $conn->real_escape_string($_GET['to'])   : '';
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

$where = [];
if($from && $to){
    $where[] = "DATE(al.timestamp) BETWEEN '$from' AND '$to'";
}
if($search){
    $where[] = "(s.name LIKE '%$search%' OR s.class LIKE '%$search%' OR al.card_id LIKE '%$search%')";
}
$whereSql = $where ? "WHERE ".implode(" AND ", $where) : "";

// Query data
$query = $conn->query("
    SELECT al.timestamp, s.name, s.class, al.card_id, al.device_id, al.schedule_status
    FROM attendance_log al
    LEFT JOIN students s ON s.id = al.student_id
    $whereSql
    ORDER BY al.timestamp DESC
");

// Set header untuk CSV download
$filename = "attendance_export_" . date('Ymd_His') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename='.$filename);

// Output ke buffer
$output = fopen('php://output', 'w');
// Header kolom
fputcsv($output, ['Tanggal/Waktu','Nama','Kelas','Card ID','Device','Status Jadwal']);

// Data
while($row = $query->fetch_assoc()){
    fputcsv($output, [
        $row['timestamp'],
        $row['name'],
        $row['class'],
        $row['card_id'],
        $row['device_id'],
        $row['schedule_status']
    ]);
}
fclose($output);
exit;
