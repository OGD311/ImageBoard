<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

$mysqli = $_DB; 

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    
    $sql = "UPDATE posts SET title = ?, rating = ?, updated_at = ? WHERE id = ? AND user_id = ?";
    

    $stmt = $mysqli->stmt_init();

    if (!$stmt->prepare($sql)) {
        die("SQL Error: " . $mysqli->error);
    }


    $title = $mysqli->real_escape_string($_POST['title']);
    $rating = $mysqli->real_escape_string($_POST['rating']);

    if (! is_numeric($rating) && $rating >= 0 && $rating <= 2) {
        die('Please enter a valid rating');
    }

    $updatedAt = time();
    $post_id = (int)$_POST['post_id'];
    $user_id = (int)$_POST['user_id'];


    $stmt->bind_param("siiii", $title, $rating, $updatedAt, $post_id, $user_id);


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
