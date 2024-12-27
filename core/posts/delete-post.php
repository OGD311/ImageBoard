<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

$mysqli = $_DB; 
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    
    $post_id = (int)$_POST['post_id'];
    $user_id = (int)$_POST['user_id'];

    $mysqli->begin_transaction();

    try {
        $deleteCommentsSql = "DELETE FROM comments WHERE post_id = ?";
        $stmtComments = $mysqli->prepare($deleteCommentsSql);
        $stmtComments->bind_param("i", $post_id);
        $stmtComments->execute();

        $deleteFavouritesSql = "DELETE FROM favourites WHERE post_id = ? ";
        $stmtFavourites = $mysqli->prepare($deleteFavouritesSql);
        $stmtFavourites->bind_param("i", $post_id);
        $stmtFavourites->execute();

        $deletePostSql = "DELETE FROM posts WHERE id = ? AND user_id = ?";
        $stmtPost = $mysqli->prepare($deletePostSql);
        $stmtPost->bind_param("ii", $post_id, $user_id);
        $stmtPost->execute();

        $mysqli->commit();
        $mysqli->close();

        header('Location: /core/main.php');
        exit(); 

    } catch (mysqli_sql_exception $exception) {
        $mysqli->rollback();
        die("Error deleting post or comments: " . htmlspecialchars($exception->getMessage()));
    
    }
} else {
    header('Location: /core/main.php');
    exit();
}
?>
