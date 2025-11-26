<?php
include 'db.php';

if (isset($_POST['new_mode'])) {
  $mode = intval($_POST['new_mode']);
  $conn->query("UPDATE settings SET test_mode = $mode WHERE id = 1");

  // Auto-clear data when enabling Test Mode
  if ($mode === 1) {
    $file = __DIR__ . '/test_data.json';
    if (file_exists($file)) {
      file_put_contents($file, json_encode([]));
    }
  }
}

header("Location: index.php?page=dashboard");
exit;
?>