<?php
include 'db.php';

// CREATE
if(isset($_POST['create'])){
    $day   = $_POST['day'];
    $in    = $_POST['time_in'];
    $out   = $_POST['time_out'];
    $grace = (int)$_POST['grace_period'];
    $holiday = isset($_POST['is_holiday']) ? 1 : 0;

    $conn->query("INSERT INTO schedules (day,time_in,time_out,grace_period,is_holiday)
                  VALUES ('$day','$in','$out',$grace,$holiday)");
    header("Location: index.php?page=schedule");
    exit;
}

// UPDATE
if(isset($_POST['update'])){
    $id    = (int)$_POST['id'];
    $day   = $_POST['day'];
    $in    = $_POST['time_in'];
    $out   = $_POST['time_out'];
    $grace = (int)$_POST['grace_period'];
    $holiday = isset($_POST['is_holiday']) ? 1 : 0;

    $conn->query("UPDATE schedules SET
        day='$day', time_in='$in', time_out='$out',
        grace_period=$grace, is_holiday=$holiday
        WHERE id=$id");
    header("Location: index.php?page=schedule");
    exit;
}

// DELETE
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM schedules WHERE id=$id");
    header("Location: index.php?page=schedule");
    exit;
}
