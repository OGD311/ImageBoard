<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] === "GET") {

    $mysqli = $_DB;

    if (isset($_GET['post_id'])) {

        $post_id = $mysqli->real_escape_string((int)$_GET['post_id']);
    
        $sql = sprintf(
            "SELECT 
                p.*, 
                u.username, 
                GROUP_CONCAT(t.name SEPARATOR ', ') as tags
            FROM 
                posts p
            LEFT JOIN 
                post_tags pt ON p.id = pt.post_id
            LEFT JOIN 
                tags t ON pt.tag_id = t.id
            LEFT JOIN 
                users u ON p.user_id = u.id
            WHERE 
                p.id = '%s'
            GROUP BY 
                p.id, u.id;
            ",
            $post_id
        );
    
        $result = $mysqli->query($sql);
    
        if (!$result) {
            die("Query failed: " . $mysqli->error);
        }
    
        $post = $result->fetch_assoc();
    
        if (!$post) {
            header("Location: upload.php");
            exit();
        }

        $uploader = $post['username'] ? ['id' => $post['user_id'], 'username' => $post['username']] : null;
    
    } else {
        header('Location: /core/main.php');
        exit();
    }

    if ( ! isset($_SESSION['user_id'])) {
        header('Location: ../errors/post-edit.php');
        exit();
    }

    
    if (($_SESSION['user_id'] != $post['user_id']) &&  (! is_admin($_SESSION['user_id'])) ) {
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
    <title>Editing Post <?= $post['id'] ?></title>
    <?php include '../html-parts/header-elems.php' ?>
</head>
<body>
    <?php include '../html-parts/nav.php'; ?>

    <h1>Editing: '<?= $post['title'] ?>'</h1>

    <form action="edit-post.php" method="post">

        <!-- <input type="hidden" name="MAX_FILE_SIZE" value="1048576"> -->
        <input type="hidden" name="user_id" value="<?= $uploader['id'] ?>">
        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
        <label for="title">Post title</label>
        <input type="text" id="title" name="title" value="<?= $post['title'] ?>">
        <br>

        <label for="rating">Post rating:</label>
        <select id="rating" name="rating">
            <option value="0" <?= ($post['rating'] == 0) ? 'selected' : '' ?>>Safe</option>
            <option value="1" <?= ($post['rating'] == 1) ? 'selected' : '' ?>>Questionable</option>
            <option value="2" <?= ($post['rating'] == 2) ? 'selected' : '' ?>>Explicit</option>
        </select>

        <br>

        <div id="tagAutocomplete">
            <input id="tagSearch" class="form-control me-2 dropdown-toggle" autocomplete="off" type="search" name="tags" placeholder="Enter Tags" 
                value="<?= str_replace(', ', ' ', $post['tags']) ?>" 
                aria-label="Search" style="width: 50%;">

            <ul id="autocompleteBox" class="dropdown-menu " aria-labelledby="dropdownMenuButton"></ul>
        </div>
        
        <button  class="btn btn-primary">Save</button>

    </form>

    <form action="delete-post.php" method="post" onsubmit="return confirm('Delete Post?');">
        <input type="hidden" name="user_id" value="<?= $uploader['id'] ?>">
        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
        <br>

        <button  class="btn btn-danger">Delete Post</button>
    </form>

    <br>
    <a href="/core/posts/view.php?post_id=<?= $post['id'] ?>" class="btn btn-secondary">Close</a>
 
    
</body>

    <?php include '../html-parts/footer.php'; ?>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const searchBox = document.querySelector("#tagSearch");
            if (searchBox) {
                autocomplete(searchBox);
            }
        });
    </script>

</html>