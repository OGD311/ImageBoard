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
            
            <div class="site-details">

                <?php if ($posts_count > 1 || $posts_count == 0): ?>
                    <p>Currently serving: <?= $posts_count ?> posts</p>
                <?php else: ?>
                    <p>Currently serving: <?= $posts_count ?> post</p>
                <?php endif; ?>

                
                <form role="search" action="/core/search-posts.php" method="post">
                    <input id="mainSearch"  autocomplete="off" type="search" name="search" placeholder="Enter Tags" value="" aria-label="Search" style="width: 50%;">
                    <ul id="autocompleteBox"  aria-labelledby="dropdownMenuButton"></ul>
                    <button type="submit">Search</button>
                </form>
            
            </div>

            <?php
                echo '<div class="counter">';
                foreach ($posts_count_array as $counter) {
                    echo '<img src="/static/images/counter/' . $counter . '.png" alt="Post Image">';
                }
                echo '</div>';
            ?>

        </div>

        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const searchBox = document.querySelector("#mainSearch");
                if (searchBox) {
                    autocomplete(searchBox);
                }
            });
        </script>


        <?php include 'core/html-parts/footer.php'; ?>

    </body>
</html>