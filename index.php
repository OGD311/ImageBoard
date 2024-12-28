<?php 
require_once 'config.php';

session_start();

$posts_count = total_posts_count();
$posts_count_array = str_split((string)$posts_count);


?>



<!DOCTYPE html>
<html>
    <head>
        <title>Home</title>
        <?php include 'core/html-parts/header-elems.php' ?>
        <meta charset="UTF-8">
    </head>

    <body>
        <?php include 'core/html-parts/nav.php'; ?>

        <div>
            <?php
            foreach ($posts_count_array as $counter) {
                echo '<div>';
                echo '<img decoding="async" src="/static/images/counter/' . $counter . '.png" alt="Post Image">';
                echo '</div>';
            }

            ?>
            <?php if ($posts_count > 1 || $posts_count == 0): ?>
                <p>Currently serving: <?= $posts_count ?> posts</p>
            <?php else: ?>
                <p>Currently serving: <?= $posts_count ?> post</p>
            <?php endif; ?>

        </div>


        <?php include 'core/html-parts/footer.php'; ?>

    </body>
</html>