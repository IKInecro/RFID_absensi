<?php
// action_schedule.php
include 'db.php';

// CREATE
if(isset($_POST['create'])){
    $day = $_POST['day'];
    $time_in = $_POST['time_in'];
    $time_out = $_POST['time_out'];
    $grace = $_POST['grace_period'] ?: 0;
    $holiday = isset($_POST['is_holiday']) ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO schedules (day,time_in,time_out,grace_period,is_holiday) VALUES (?,?,?,?,?)
        ON DUPLICATE KEY UPDATE time_in=VALUES(time_in), time_out=VALUES(time_out), grace_period=VALUES(grace_period), is_holiday=VALUES(is_holiday)");
    $stmt->bind_param("sssii",$day,$time_in,$time_out,$grace,$holiday);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?page=schedules");
    exit;
}

// DELETE
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM schedules WHERE id=$id");
    header("Location: index.php?page=schedules");
    exit;
}
