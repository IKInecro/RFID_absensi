<?php
// action_student.php
include 'db.php';

// upload config
$uploadDir = __DIR__ . '/uploads/';
$allowed = ['image/jpeg','image/png','image/webp'];
$maxSize = 2 * 1024 * 1024; // 2MB

function handleUpload($fieldName, $uploadDir, $allowed, $maxSize, $oldFile = null){
    if(!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) return $oldFile;
    $f = $_FILES[$fieldName];

    if($f['size'] > $maxSize) return $oldFile;
    if(!in_array(mime_content_type($f['tmp_name']), $allowed)) return $oldFile;

    // create uploads dir
    if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
    $name = uniqid('p_') . '.' . $ext;
    $dest = $uploadDir . $name;

    if(move_uploaded_file($f['tmp_name'], $dest)){
        // delete old file if exists
        if($oldFile && file_exists($uploadDir . $oldFile)){
            @unlink($uploadDir . $oldFile);
        }
        return $name;
    }
    return $oldFile;
}

// CREATE
if(isset($_POST['create'])){
    $name  = $conn->real_escape_string($_POST['name']);
    $class = $conn->real_escape_string($_POST['class']);
    $card  = $conn->real_escape_string($_POST['card_id']);
    $status = isset($_POST['status']) ? $conn->real_escape_string($_POST['status']) : 'active';

    $profile = handleUpload('profile_pic', $uploadDir, $allowed, $maxSize);

    $stmt = $conn->prepare("INSERT INTO students (name,class,card_id,status,profile_pic) VALUES (?,?,?,?,?)");
    $stmt->bind_param("sssss", $name, $class, $card, $status, $profile);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?page=students");
    exit;
}

// UPDATE
if(isset($_POST['update'])){
    $id    = (int)$_POST['id'];
    $name  = $conn->real_escape_string($_POST['name']);
    $class = $conn->real_escape_string($_POST['class']);
    $card  = $conn->real_escape_string($_POST['card_id']);
    $status = isset($_POST['status']) ? $conn->real_escape_string($_POST['status']) : 'active';

    // get old file
    $old = $conn->query("SELECT profile_pic FROM students WHERE id=$id")->fetch_assoc()['profile_pic'] ?? null;
    $profile = handleUpload('profile_pic', $uploadDir, $allowed, $maxSize, $old);

    $stmt = $conn->prepare("UPDATE students SET name=?, class=?, card_id=?, status=?, profile_pic=? WHERE id=?");
    $stmt->bind_param("sssssi", $name, $class, $card, $status, $profile, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?page=students");
    exit;
}

// DELETE
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];

    // delete file if exists
    $row = $conn->query("SELECT profile_pic FROM students WHERE id=$id")->fetch_assoc();
    if($row && $row['profile_pic']){
        $f = __DIR__ . '/uploads/' . $row['profile_pic'];
        if(file_exists($f)) @unlink($f);
    }

    $conn->query("DELETE FROM students WHERE id=$id");
    header("Location: index.php?page=students");
    exit;
}
