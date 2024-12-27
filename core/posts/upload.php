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

                    <canvas id="output" style="object-fit: contain;"></canvas>';
            
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
            output.width = 400;
            output.height = 400;

            var file = event.target.files[0];
            var ctx = output.getContext('2d');
            var url = URL.createObjectURL(file);

            if (file.type.startsWith('image/')) {
                var img = new Image();
                img.onload = function() {
                    ctx.clearRect(0, 0, output.width, output.height); // Clear canvas
                    ctx.drawImage(img, 0, 0, output.width, output.height);
                    URL.revokeObjectURL(img.src);
                };
                img.src = url;
            } else if (file.type.startsWith('video/')) {
                var video = document.createElement('video');
                video.src = url;

                video.addEventListener('loadeddata', function() {
                    video.currentTime = 0; // Set to the start of the video
                    video.addEventListener('seeked', function drawFrame() {
                        ctx.clearRect(0, 0, output.width, output.height); // Clear canvas
                        ctx.drawImage(video, 0, 0, output.width, output.height);
                        URL.revokeObjectURL(video.src);
                        video.removeEventListener('seeked', drawFrame); // Cleanup listener
                    });
                    video.currentTime = 0; // Trigger the seeked event to draw the first frame
                });
            }
        };

    </script>

    <?php include '../html-parts/footer.php'; ?>
    
</body>
</html>