<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

$mysqli = $_DB; 
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    
    $sql = "INSERT INTO comments (post_id, user_id, comment, posted_at) VALUES (?, ?, ?, ?)";


    $stmt = $mysqli->stmt_init();

    if (!$stmt->prepare($sql)) {
        die("SQL Error: " . $mysqli->error);
    }

    $postId = (int)$_POST['post_id'];
    $userId = (int)$_POST['user_id'];
    $comment = $mysqli->real_escape_string(substr($_POST['comment'], 0, 144));
    $postedAt = time();

    $stmt->bind_param("iisi", $postId, $userId, $comment, $postedAt);


    if ($stmt->execute()) {
        $mysqli->close();
        header('Location: /core/posts/view.php?post_id=' . $postId);
        exit(); 
    } else {
        die("Error updating post: " . $stmt->error);
    }
} else {
    header('Location: /core/main.php');
    exit();
}
?>
