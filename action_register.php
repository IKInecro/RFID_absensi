<?php
// action_register.php
include 'db.php';

if(isset($_POST['toggle_reg_mode'])){
    $mode = $_POST['toggle_reg_mode'] === "1" ? 1 : 0;
    $conn->query("UPDATE settings SET reg_mode=$mode WHERE id=1");
    header("Location: index.php?page=dashboard");
    exit;
}
