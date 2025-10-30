<?php
header('Content-Type: application/json');
$file = '../test_data.json';

if (isset($_GET['clear']) && $_GET['clear'] == '1') {
  if (file_exists($file)) file_put_contents($file, json_encode([]));
  echo json_encode(['success'=>true,'message'=>'Data tester dibersihkan']); exit;
}

if (file_exists($file)) {
  $data = json_decode(file_get_contents($file), true);
  echo json_encode(['records'=>$data]);
} else {
  echo json_encode(['records'=>[]]);
}
?>
