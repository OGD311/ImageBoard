<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

$mysqli = $_DB;


if ($_SERVER['REQUEST_METHOD'] === "POST") {

    if ($_POST['action'] === 'add') {
        $sql = "INSERT INTO favourites (post_id, user_id) VALUES (?, ?)";
    } else if ($_POST['action'] === 'remove') {
        $sql = "DELETE FROM favourites WHERE post_id = ? AND user_id = ?";
    } else {
        die("Invalid action");
    }

    $post_id = (int)htmlspecialchars($_POST['post_id']);
    $user_id = (int)htmlspecialchars($_POST['user_id']);

    $stmt = $mysqli->stmt_init();
    $stmt->prepare($sql);
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
    $stmt->close();
}