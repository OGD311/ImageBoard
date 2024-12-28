<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

session_start();

$mysqli = $_DB;

if (!isset($_SESSION['user_id']) || !is_admin($_SESSION['user_id'])) {
    header('Location: /core/users/login.php');
    exit();
}

// Disable foreign key checks
$mysqli->query("SET foreign_key_checks = 0");

// Update posts and reassign IDs
$mysqli->query("
    CREATE TEMPORARY TABLE temp_posts AS
    SELECT id AS old_id, ROW_NUMBER() OVER (ORDER BY id) AS new_id
    FROM posts;
");

$mysqli->query("
    UPDATE posts
    JOIN temp_posts ON posts.id = temp_posts.old_id
    SET posts.id = temp_posts.new_id;
");

// Update tags and reassign IDs
$mysqli->query("
    CREATE TEMPORARY TABLE temp_tags AS
    SELECT id AS old_id, ROW_NUMBER() OVER (ORDER BY id) AS new_id
    FROM tags;
");

$mysqli->query("
    UPDATE tags
    JOIN temp_tags ON tags.id = temp_tags.old_id
    SET tags.id = temp_tags.new_id;
");

// Update post_tags based on new post and tag IDs
$mysqli->query("
    UPDATE post_tags
    JOIN temp_posts ON post_tags.post_id = temp_posts.old_id
    JOIN temp_tags ON post_tags.tag_id = temp_tags.old_id
    SET post_tags.post_id = temp_posts.new_id,
        post_tags.tag_id = temp_tags.new_id;
");

// Update comments based on new post IDs
$mysqli->query("
    UPDATE comments
    JOIN temp_posts ON comments.post_id = temp_posts.old_id
    SET comments.post_id = temp_posts.new_id;
");

// Update favourites based on new post IDs
$mysqli->query("
    UPDATE favourites
    JOIN temp_posts ON favourites.post_id = temp_posts.old_id
    SET favourites.post_id = temp_posts.new_id;
");

// Re-enable foreign key checks
$mysqli->query("SET foreign_key_checks = 1");

// Update AUTO_INCREMENT values
$maxId = $mysqli->query("SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM posts")->fetch_assoc()['next_id'];
$mysqli->query("ALTER TABLE posts AUTO_INCREMENT = $maxId");

$maxId = $mysqli->query("SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM tags")->fetch_assoc()['next_id'];
$mysqli->query("ALTER TABLE tags AUTO_INCREMENT = $maxId");

$maxId = $mysqli->query("SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM comments")->fetch_assoc()['next_id'];
$mysqli->query("ALTER TABLE comments AUTO_INCREMENT = $maxId");

// Redirect to main page
header('Location: main.php');
exit();