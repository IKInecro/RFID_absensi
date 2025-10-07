<?php
include 'db.php';

if (isset($_POST['new_mode'])) {
  $mode = intval($_POST['new_mode']);
  $conn->query("UPDATE settings SET test_mode = $mode WHERE id = 1");
}

header("Location: index.php?page=dashboard");
exit;
?>
