<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

$mysqli = $_DB; 
if ($_SERVER['REQUEST_METHOD'] === "POST") {

    session_start();
    
    try {

        $mysqli->begin_transaction();
    
        $user_id = (int)$_POST['user_id'];
    

        $deletePostsSql = "DELETE FROM posts WHERE user_id = ?";
        if ($postsStmt = $mysqli->prepare($deletePostsSql)) {
            $postsStmt->bind_param("i", $user_id);
            $postsStmt->execute();
            $postsStmt->close();
        } else {
            throw new Exception("SQL Error for posts: " . htmlspecialchars($mysqli->error));
        }
    

        $deleteCommentsSql = "DELETE FROM comments WHERE user_id = ?";
        if ($commentsStmt = $mysqli->prepare($deleteCommentsSql)) {
            $commentsStmt->bind_param("i", $user_id);
            $commentsStmt->execute();
            $commentsStmt->close();
        } else {
            throw new Exception("SQL Error for comments: " . htmlspecialchars($mysqli->error));
        }
    

        $sql = "DELETE FROM users WHERE id = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {

                $mysqli->commit();
                if ($_SESSION['user_id'] == $user_id) {
                    session_destroy();
                }
                header('Location: /core/main.php');
                exit(); 

            } else {
                throw new Exception("Error deleting account: " . htmlspecialchars($stmt->error));
            }
        } else {
            throw new Exception("SQL Error for user: " . htmlspecialchars($mysqli->error));
        }
    
    } catch (Exception $e) {

        $mysqli->rollback();
        die("Transaction failed: " . htmlspecialchars($e->getMessage()));
    }
    

} else {
    header('Location: /core/main.php');
    exit();
}
