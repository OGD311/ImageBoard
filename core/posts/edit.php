<?php
require_once '../config.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] === "GET") {

    $mysqli = $_DBPATH;

    if (isset($_GET['post_id'])) {

        $postId = $mysqli->real_escape_string((int)$_GET['post_id']);
    
        $sql = sprintf(
            "SELECT p.*, u.username 
             FROM posts p 
             LEFT JOIN users u ON p.user_id = u.id 
             WHERE p.id = '%s'",
            $postId
        );
    
        $result = $mysqli->query($sql);
    
        if (!$result) {
            die("Query failed: " . $mysqli->error);
        }
    
        $post = $result->fetch_assoc();
    
        if (!$post) {
            header("Location: upload.html");
            exit();
        }

        $uploader = $post['username'] ? ['id' => $post['user_id'], 'username' => $post['username']] : null;
    
    } else {
        header('Location: http://localhost:8080/core/index.php');
        exit();
    }

    if ( ! isset($_SESSION['user_id'])) {
        header('Location: ../errors/post-edit.php');
        exit();
    }

    
    if (($_SESSION['user_id'] !== $post['user_id']) && is_admin($_SESSION['user_id']) !== "1") {
        header('Location: ../errors/post-edit.php');
        exit();
    }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post <?= $post['id'] ?></title>
    <?php include '../html-parts/header-elems.php' ?>
</head>
<body>
    <?php include '../html-parts/nav.php'; ?>

    <h1>Editing: <?= $post['title'] ?></h1>

    <form action="edit-post.php" method="post">

        <!-- <input type="hidden" name="MAX_FILE_SIZE" value="1048576"> -->
        <input type="hidden" name="user_id" value="<?= $uploader['id'] ?>">
        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
        <label for="title">Post title</label>
        <input type="text" id="title" name="title" value="<?= $post['title'] ?>">
        <br>
        
        <button>Save</button>

    </form>

    <form action="delete-post.php" method="post" onsubmit="return confirm('Delete Post?');">
        <input type="hidden" name="user_id" value="<?= $uploader['id'] ?>">
        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
        <br>

        <button>Delete</button>
    </form>
    
</body>