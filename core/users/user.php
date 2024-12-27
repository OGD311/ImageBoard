<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once './profiles/profile-functions.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] === "GET") {

    $mysqli = $_DB;

    if (isset($_GET['user_id'])) {
        $user_id = (int)$_GET['user_id']; 
    
        $userQuery = sprintf("
        SELECT id, username 
        FROM users 
        WHERE id = '%s'", 
        $mysqli->real_escape_string($user_id));
     
    
    
        $userResult = $mysqli->query($userQuery);
        $userData = $userResult->fetch_assoc();

        if (! $userData) {
            header('Location: ../errors/user-view.php');
        }
        

        $postsQuery = sprintf("
            SELECT p.* 
            FROM posts p 
            WHERE p.user_id = '%s' 
            ORDER BY p.uploaded_at DESC
            LIMIT " . ($GLOBALS['_POSTS_PER_PAGE']) . ";", 
            $mysqli->real_escape_string($user_id)
        );
        
        $postsResult = $mysqli->query($postsQuery);
        $postsData = [];
        while ($post = $postsResult->fetch_assoc()) {
            $postsData[] = $post;
        }
        

        $commentsData = [];
        $commentsQuery = sprintf("
            SELECT c.* 
            FROM comments c 
            WHERE  c.user_id = '%s' 
            ORDER BY c.posted_at DESC
            LIMIT " . ($GLOBALS['_COMMENTS_PER_PAGE']) . ";", 
            $mysqli->real_escape_string($user_id)
        );
    
        $commentsResult = $mysqli->query($commentsQuery);
        while ($comment = $commentsResult->fetch_assoc()) {
            $commentsData[] = $comment;
        }
        
    } else {
        header("Location: ../main.php");
        exit();
    }
    

} else {
    header("Location: ../main.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $userData['username'] ?>'s profile</title>
    <?php include '../html-parts/header-elems.php' ?>
    <link rel="stylesheet" href="/static/css/ratings.css">
</head>
<body>
    <?php include '../html-parts/nav.php'; ?>

    <h1><?= $userData['username'] ?></h1>

    <div>

    <?php 
        echo '<img src="/storage/profilepictures/' . get_profile_picture($userData['id']) . '.jpg" alt="Profile Picture" width="200" height="200" style="object-fit: contain;">';
    ?>
    <h3><?= get_display_name($userData['id'])?></h3>
        <p><?= get_bio($userData['id'])?></p>

        <a class="btn btn-danger" href=./profiles/edit-profile.php>Edit Profile</a>
    </div>

    <h2><?= $userData['username'] ?>'s latest Posts</h2>
    <div id="posts" class="container-fluid text-center row justify-content-center">
        <?php

            if ($postsData) {
                foreach ($postsData as $post) {

                    $apply_blur = '';

                    if ($post['rating'] == 2) {
                        $apply_blur = 'blur-explicit';
                    }
                    
                    echo '
                        <div class="card justify-content-center border-2 m-1" style="width: 12rem;">
                        <a href="/core/posts/view.php?post_id=' . $post['id'] . '">
                        <img class="card-img-top ' . $apply_blur . '" src="/storage/thumbnails/' . htmlspecialchars($post['filehash']) . "-thumb.jpg" . '" alt="Post Image" width=200 height=200 style="object-fit: contain; padding-top: 10px; padding-bottom: 2px;">
                        </a>
                        <span style="display: flex; align-items: center; gap: 10px;">
                            <img src="/static/svg/comment-icon.svg" alt="Description of the icon" width="16" height="16">
                            <p style="margin: 0;">'. $post['comment_count'] . '</p>
                            <p style="margin: 0;" class="rating-' . $post['rating'] . '">' . get_rating_text($post['rating'], true) . '</p>
                        </span>
                        </div>';
                }

            } elseif (count($postsData) === 0) {
                echo "<p>User has no posts yet!</p>";
            } else {
                echo "<p>Error: " . htmlspecialchars($mysqli->error) . "</p>";
            }

            
        ?>
    </div>
    <h2><?= $userData['username'] ?>'s latest Comments</h2>

        <?php
            if ($commentsData) {

                foreach ($commentsData as $comment) {
                    echo "<p><strong>" . htmlspecialchars($comment['comment']) . 
                    "</strong> on <a href='/core/posts/view.php?post_id=" . 
                    htmlspecialchars($comment['post_id']) . "'>" . "'"
                     . post_title($comment['post_id']) . "'" ."</a> at "  
                     . date("h:i a", $comment['posted_at']) . " on " 
                     . date("d/m/y", $comment['posted_at']) . "</p>";
                }

            } elseif (count($commentsData) === 0) {
                echo "<p>User has no comments yet!</p>";
            } else {
                echo "<p>Error: " . htmlspecialchars($mysqli->error) . "</p>";
            }
        ?>


    <!-- <form action="edit-user.php" method="post">

        <input type="hidden" name="user_id" value="<?= $userData['id'] ?>">
        <label for="title">Update Username</label>
        <input type="text" id="title" name="title" value="<?= $userData['username'] ?>">
        <label for="title">Update Password</label>
        <input type="password" id="password" name="password" value="">
        <br>

        <button>Save</button>

    </form> -->
    
    <?php
        if (isset($_SESSION['user_id'])) {
            if ($_SESSION['user_id'] == $userData['id'] || is_admin($_SESSION['user_id'])) {
                echo '<form action="delete-user.php" method="post" onsubmit="return confirm(\'Delete Account?\');">
                        <input type="hidden" name="user_id" value="' . htmlspecialchars($userData['id']) . '">
                        <button type="submit">Delete Account</button>
                    </form>';

            }
        }
    ?>
    
    <?php include '../html-parts/footer.php'; ?>
</body>