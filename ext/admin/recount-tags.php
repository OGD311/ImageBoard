<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

session_start();

$mysqli = $_DB;

if (! isset($_SESSION['user_id']) && !is_admin($_SESSION['user_id'])) {
    header('Location: /core/users/login.php');
    exit();
}

$sql = "DELETE FROM post_tags
WHERE post_id NOT IN (SELECT id FROM posts);";

$stmt = $mysqli->prepare($sql);
if ($stmt) {
    $stmt->execute();
} else {
    exit($mysqli->error);
}

$sql = "UPDATE tags
SET count = (
    SELECT COUNT(*)
    FROM post_tags
    WHERE post_tags.tag_id = tags.id
);";

$stmt = $mysqli->prepare($sql);
if ($stmt) {

    $stmt->execute();

    $result = $stmt->get_result();

} else {
    exit($mysqli->error);
}



header('Location: main.php');
exit();