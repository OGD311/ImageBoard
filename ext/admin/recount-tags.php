<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

session_start();

$mysqli = $_DB;

if (!isset($_SESSION['user_id']) || !is_admin($_SESSION['user_id'])) {
    header('Location: /core/users/login.php');
    exit();
}

// Step 1: Delete invalid post_tags
$sql = "DELETE FROM post_tags WHERE post_id NOT IN (SELECT id FROM posts);";
$stmt = $mysqli->prepare($sql);
if ($stmt) {
    $stmt->execute();
} else {
    exit($mysqli->error);
}

// Step 2: Update counts for original tags
$sql = "
    UPDATE tags
    SET count = (
        SELECT COUNT(*)
        FROM post_tags
        WHERE post_tags.tag_id = tags.id
    );
";
$stmt = $mysqli->prepare($sql);
if ($stmt) {
    $stmt->execute();
} else {
    exit($mysqli->error);
}

// Step 3: Use a temporary table to update aliased tags
$sql = "
    CREATE TEMPORARY TABLE temp_tag_counts AS
    SELECT ta.new_tag AS tag_id, t.count AS count
    FROM tags t
    JOIN tag_aliases ta ON t.id = ta.old_tag;
";
$stmt = $mysqli->prepare($sql);
if ($stmt) {
    $stmt->execute();
} else {
    exit($mysqli->error);
}

$sql = "
    UPDATE tags
    JOIN temp_tag_counts ON tags.id = temp_tag_counts.tag_id
    SET tags.count = temp_tag_counts.count;
";
$stmt = $mysqli->prepare($sql);
if ($stmt) {
    $stmt->execute();
} else {
    exit($mysqli->error);
}

// Redirect to main page
header('Location: main.php');
exit();
