<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
session_start();

if (empty($_POST['username'])) {
    die('Username cannot be empty');
}

if ( strlen($_POST['password']) < 8) {
    die('Password must be longer than 8 characters');
}

if ( ! preg_match('/[a-z]/i', $_POST['password'])) {
    die('Password must contain at least one letter');
}

if ( ! preg_match('/[0-9]/i', $_POST['password'])) {
    die('Password must contain at least one number');
}

if ( $_POST['password'] !== $_POST['password_confirmation']) {
    die('Passwords do not match');
}

$password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);


$mysqli = $_DB;

$sql = "INSERT INTO users (username, password_hash, created_at) VALUES (?, ?, ?)";

$stmt = $mysqli->stmt_init();

if ( ! $stmt->prepare($sql) ) {
    die('SQL Error: ' . $mysqli->error);
}
$timestamp = time();

$stmt->bind_param('ssi', $_POST['username'], $password_hash, $timestamp);

if ($stmt->execute()) {
    $user_id = get_user_id($_POST['username']);
    

    session_regenerate_id();

    $_SESSION['user_id'] = $user_id;

    header('Location: user.php?user_id=' . $user_id);
    exit;
}
else {
    if ($mysqli->errno === 1062) {
        die('Username already taken');
    }
    die($mysqli->error . ' ' . $mysqli->errno);
}


