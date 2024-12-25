<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once '../core/retrieve-posts.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/favourites/favourites-functions.php';

session_start();

$mysqli = $_DB;

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    
    if (isset($_GET['search'])) {
        $searchString = trim($_GET['search']);
        $searchString = str_replace(' ', '+', $searchString);
        $searchList = explode('+', $searchString);

    } else {
        header("Location: main.php?page=1&search=");
        exit();
    }

    if (! empty($searchList)) {
        if (preg_match('/order\s*:\s*\'?(.+?)(\+|$)/', $searchString, $matches)) {
            $order_by = $mysqli->real_escape_string($matches[1]);

        } else {
            $order_by = 'upload-desc';
        }
    } else {
        $order_by = 'upload-desc';
    }



    if (isset($_GET['page'])) {
        $current_page_number = intval($_GET['page']);
    } else {
        $current_page_number = 1;
    }
    
    $result = get_posts($searchList, $current_page_number, true); 

    $posts = $result['posts'];      
    $total_posts = $result['total_posts'];


    $number_of_pages = number_of_pages($total_posts);

    if ($current_page_number > $number_of_pages) {
        header('Location: main.php?page='. $number_of_pages .'&search=' . $_GET['search']);
    }



} else {
    header("Location: main.php?page=1&search=");
    exit();
}

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Posts</title>
        <?php include 'html-parts/header-elems.php' ?>
        <link rel="stylesheet" href="/static/css/ratings.css">
        <link rel="stylesheet" href="/static/css/tags.css">
        <meta charset="UTF-8">
    </head>

    <body>
        <?php include 'html-parts/nav.php'; ?>
        
        <br>

        <div id="headings" class="contain container-fluid d-flex justify-content-between align-items-center">
            
            <h1 class="mb-0">Latest Posts</h1>

            <form name="order_by" class="d-flex">
                <label for="sort-options">Sort posts by:</label>
                <select id="sort-options" style="margin-left: 10px;" onchange="sort_posts(this.value, '<?= $searchString ?>')">
                    <option value="upload-desc" <?= ($order_by == 'upload-desc') ? 'selected' : '' ?>>Upload date ↑</option>
                    <option value="upload-asc" <?= ($order_by == 'upload-asc') ? 'selected' : '' ?>>Upload date ↓</option>

                    <option value="updated-desc" <?= ($order_by == 'updated-desc') ? 'selected' : '' ?>>Updated at ↑</option>
                    <option value="updated-asc" <?= ($order_by == 'updated-asc') ? 'selected' : '' ?>>Updated at ↓</option>

                    <option value="comments-desc" <?= ($order_by == 'comments-desc') ? 'selected' : '' ?>>Comments ↑</option>
                    <option value="comments-asc" <?= ($order_by == 'comments-asc') ? 'selected' : '' ?>>Comments ↓</option>
                </select>
              
            </form>

        </div>
        <br>

        <div class='contain'>

        <div class="left-div">
            <?php include 'tags/tag-all.php'; ?>
        </div>


        <div id="posts" class="right-div container-main text-center row justify-content-center">
            <?php
                if ($result) {
                    foreach ($posts as $post) {
                        
                        $apply_blur = $post['rating'] == 2 ? 'blur-explicit' : '';
                        
 
                        $filehash = htmlspecialchars($post['filehash']);
                        $imageSrc = "/storage/thumbnails/{$filehash}-thumb.jpg";
                        
 
                        $cardContent = [
                            '<div class="card justify-content-center border-2 m-1" style="width: 12rem;">',
                                '<a href="/core/posts/view.php?post_id=' . $post['id'] . '&search='. $searchString . '">',
                                    '<img class="card-img-top ' . $apply_blur . '" src="' . $imageSrc . '" alt="Post Image" width="200" height="200" decoding="async" style="object-fit: contain; padding-top: 10px; padding-bottom: 2px;" loading="lazy">',
                                    (strtolower($post['extension']) != 'png' && strtolower($post['extension']) != 'jpg' && strtolower($post['extension']) != 'jpeg') ? '<p class="extension-tag">' . $post['extension'] . '</p>'  : '',
                                '</a>',
                                '<span style="display: flex; align-items: center; gap: 10px;">',
                                    '<img src="/static/svg/comment-icon.svg" alt="Description of the icon" width="16" height="16">',
                                    '<p style="margin: 0;">' . $post['comment_count'] . '</p>',

                                    '<p style="margin: 0;" class="rating-' . $post['rating'] . '">' . get_rating_text($post['rating'], true) . '</p>',

                                    
                                    is_favourite($post['id'], $_SESSION['user_id']) ? 
                                    '<a id="removeFavourite" onclick="remove_from_favourites(' . $post['id'] . ' , ' . $_SESSION['user_id'] . ')">
                                    <img src="/static/svg/heart-fill-icon.svg" alt="Description of the icon" width="16" height="16">
                                    </a>' 
                                    : 
                                    '<a id="addFavourite" onclick="add_to_favourites(' . $post['id'] . ' , ' . $_SESSION['user_id'] . ')">
                                    <img src="/static/svg/heart-empty-icon.svg" alt="Description of the icon" width="16" height="16" >
                                    </a>',

                                '</span>',
                            '</div>'
                        ];
              
                        echo implode('', $cardContent);
                    }
                } else {
                    echo "<p>Error: " . htmlspecialchars($mysqli->error) . "</p>";
                }
                if ($current_page_number == $number_of_pages && ($total_posts > 0)) {
                    echo "<p>You've reached the end!<br>If you got here from just scrolling I would be concerned...<br><a href='main.php?page=1'>Go Home</a></p>";
                }
            ?>
        
        
        <br>

        <div id="pages-buttons" class="container-fluid text-center row justify-content-center">

            <?php

                $current_page_number = max(1, $current_page_number); 

                if ($current_page_number > $number_of_pages) {
                    echo "<span>
                    <strong> No posts to display! </strong>
                    <p>Why don't you <a href='posts/upload.php'>upload</a> one?</p>";

                } else if ($current_page_number == $number_of_pages && $current_page_number == 1) {
                    echo '<span>
                    <strong> ' . $current_page_number . ' </strong>';
                    
                } else if ($current_page_number == $number_of_pages) {
                    echo '<span>
                    <a href="main.php?page=1&search='. join("+", $searchList) .'&order_by='. $order_by .'">1</a> 

                    ... <a href="main.php?page=' . ($current_page_number - 1) . '&search='. join("+", $searchList) .'&order_by='. $order_by .'"><<</a>

                    <strong> ' . $current_page_number . ' </strong>';
                    

                } else if ($current_page_number == 1) {
                    echo '<span>
                    <strong> ' . $current_page_number .  '</strong>

                    <a href="main.php?page=' . ($current_page_number + 1) . '&search='. join("+", $searchList) .'&order_by='. $order_by .'">>></a>

                    ... <a href="main.php?page=' . ($number_of_pages) . '&search='. join("+", $searchList) .'&order_by='. $order_by .'">'. ($number_of_pages) .'</a>';

                } else {
                    echo '<span>
                    <a href="main.php?page=1&search='. join("+", $searchList) .'&order_by='. $order_by .'">1</a> 

                    ...
                     <a href="main.php?page=' . ($current_page_number - 1) . '&search='. join("+", $searchList) .'&order_by='. $order_by .'"><<</a>

                    <strong> ' . $current_page_number . ' </strong>

                    <a href="main.php?page=' . ($current_page_number + 1) . '&search='. join("+", $searchList) .'&order_by='. $order_by .'">>></a>

                    ... <a href="main.php?page=' . ($number_of_pages) . '&search='. join("+", $searchList) .'&order_by='. $order_by .'">'. ($number_of_pages) .'</a>';

                }

                if (total_posts_count() > 0) {
                    $sql = "SELECT id FROM posts ORDER BY RAND() LIMIT 1;";
                    $result = $mysqli->query($sql);

                    if ($result && $result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        echo '  <a href="/core/posts/view.php?post_id=' . $row['id'] . '">Random Post</a>';
                    } 
                }
                               

                echo '</span>';
            ?>

        </div>

        </div>

        
        </div>

        <?php include 'html-parts/footer.php'; ?>
    </body>

    <script>
        function sort_posts(orderValue, searchValue = '') {
            const url = new URL(window.location.href);
            
            const currentSearch = url.searchParams.get('search') || '';

            const updatedSearch = currentSearch.replace(/order:\s*[^+\s]*/g, '').trim();

            
            const newSearch = updatedSearch ? `${updatedSearch} order:${orderValue}` : `order:${orderValue}`;

            url.searchParams.set('search', newSearch);
            
            document.location.href = url.toString();
        }

    </script>
</html>