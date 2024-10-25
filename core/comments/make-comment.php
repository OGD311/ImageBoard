<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

$mysqli = $_DB; 
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    
    $sql = "INSERT INTO comments (post_id, user_id, comment, posted_at) VALUES (?, ?, ?, ?)";


    $stmt = $mysqli->stmt_init();

    if (!$stmt->prepare($sql)) {
        die("SQL Error: " . $mysqli->error);
    }


    $post_id = (int)$_POST['post_id'];
    $user_id = (int)$_POST['user_id'];


    $commentShort = substr($_POST['comment'], 0, $GLOBALS['_COMMENT_CHARACTER_LIMIT']);

    if (! isset($commentShort)) {
        header('Location: /core/posts/view.php?post_id=' . $post_id);
        exit();
    }
    $comment = $mysqli->real_escape_string($commentShort);

    
    $postedAt = time();

    $stmt->bind_param("iisi", $post_id, $user_id, $comment, $postedAt);


    if ($stmt->execute()) {
        $mysqli->close();
        header('Location: /core/posts/view.php?post_id=' . $post_id);
        exit(); 
    } else {
        die("Error updating post: " . $stmt->error);
    }
} else {
    header('Location: /core/main.php');
    exit();
}
?>
