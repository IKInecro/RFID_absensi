<?php
include 'db.php';

// CREATE
if (isset($_POST['create'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $class = $conn->real_escape_string($_POST['class']);
    $card  = $conn->real_escape_string($_POST['card_id']);

    $sql = "INSERT INTO students (name, class, card_id)
            VALUES ('$name','$class','$card')";
    $conn->query($sql);
    header("Location: students.php");
    exit;
}

// UPDATE
if (isset($_POST['update'])) {
    $id   = (int)$_POST['id'];
    $name = $conn->real_escape_string($_POST['name']);
    $class= $conn->real_escape_string($_POST['class']);
    $card = $conn->real_escape_string($_POST['card_id']);

    $sql = "UPDATE students
            SET name='$name', class='$class', card_id='$card'
            WHERE id=$id";
    $conn->query($sql);
    header("Location: students.php");
    exit;
}

// DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM students WHERE id=$id");
    header("Location: students.php");
    exit;
}
?>
