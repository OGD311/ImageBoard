<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

session_start();

$mysqli = $_DB;

if (isset($_SESSION['user_id'])) {

    $sql = "SELECT * FROM users WHERE id = {$_SESSION['user_id']}";

    $result = $mysqli->query($sql);

    $user = $result->fetch_assoc();

    

} else {
    header('Location: /core/users/login.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload</title>
    <?php include '../html-parts/header-elems.php' ?>
</head>
<body>
    <?php include '../html-parts/nav.php'; ?>

    <h1>Upload</h1>

        <?php

            if ($GLOBALS['_ALLOW_UPLOADS']){
                echo '
                    <form class="container-fluid" action="upload-post.php" method="post" enctype="multipart/form-data">

                       
                        <input type="hidden" name="user_id" value="' . $user["id"] . '">
                        <label for="title">Post title</label><br>
                        <input type="text" id="title" name="title" value="">
                        <br>
                        <label for="image">Image file</label><br>
                        <input type="file" id="image" name="media" accept="image/*,video/*" onchange="updateTitle(); loadFile(event);">
                        <br>
                        <label for="rating">Post rating:</label>
                        <select id="rating" name="rating">
                            <option value="0">Safe</option>
                            <option value="1">Questionable</option>
                            <option value="2" selected>Explicit</option>
                        </select>

                        <button onClick="this.form.submit(); this.disabled=true;" >Upload</button>

                    </form>

                    <img id="output" width=400 height=400 style="object-fit: contain;"/>';
            
            } else {
                echo '<p>Uploads are disabled at this time</p>';
            }
        ?>

    <script>
        function updateTitle() {
            const files = event.target.files;
            const fileName = files[0].name.replace(/\.[^/.]+$/, "");

            document.getElementById('title').value = fileName;
        }

        var loadFile = function(event) {
            var output = document.getElementById('output');
            output.src = URL.createObjectURL(event.target.files[0]);
            output.onload = function() {
            URL.revokeObjectURL(output.src)
            }
        };
    </script>

    <?php include '../html-parts/footer.php'; ?>
    
</body>
</html>