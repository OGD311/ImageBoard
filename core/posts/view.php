<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] === "GET") {

    $mysqli = $_DB;

    if (isset($_GET['post_id'])) {
        $post_id = (int)$_GET['post_id']; // Casting to integer for safety
    
        // Combined SQL query using LEFT JOIN
        $sql = sprintf("
            SELECT p.*, u.id AS uploader_id, u.username, u.is_admin 
            FROM posts p 
            LEFT JOIN users u ON p.user_id = u.id 
            WHERE p.id = '%s'", 
            $mysqli->real_escape_string($post_id)
        );
    
        $result = $mysqli->query($sql);
    
        $post = $result->fetch_assoc();
        
    
        if (!$post) {
            header("Location: ../errors/post-view.php");
            exit();
        }

        $uploader = [
            "id" => $post['uploader_id'],
            "username" => $post['username'],
            "is_admin" => $post['is_admin']
        ];
    }

    
    
    
} else {
    header('Location: /core/main.php');
    exit();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post <?= $post['id'] ?></title>
    <?php include '../html-parts/header-elems.php' ?>
</head>
<body>
    <?php include '../html-parts/nav.php'; ?>

    <div class="contain">
        
        <div class="left-div">
            <?php include '../tags/tag-view.php'; ?>
            <hr>
            <h4>Details</h4>
            <div id="details" class="">
                <ul>
                    <p>Uploaded on: <br><?= date("d/m/Y", $post['uploaded_at']) ?></p>
                        
                    <?php if ($post['updated_at']): ?>
                        <p>Last updated on: <br> <?= date("d/m/Y", $post['updated_at']) ?></p>
                    <?php endif ?>
                        
                    
                    <p>File type: <?=  $post['extension'] ?></P>
                    <p>File Resolution: <br><?= $post['file_height'] . " x " . $post['file_width'] ?></p>
                    <?php if ($uploader): ?>
                        <p>Uploaded by:<br> <a href="../users/user.php?user_id=<?php echo htmlspecialchars($uploader['id']); ?>"><?= $uploader['username'] ?></a>
                        </a></p>
                    <?php endif ?>
                    <p>Rating: <?= get_rating_text($post['rating']) ?></p>
                    <p>Post ID: <?= $post['id'] ?></p>

                    <?php if (!empty($_SESSION['user_id']) && ($uploader['id'] == $_SESSION['user_id'] || is_admin($_SESSION['user_id']))) : ?>
                        <button class="btn btn-secondary" onclick="location.href='edit.php?post_id=<?= $post['id'] ?>'">Edit Post</button>

                        <form action="delete-post.php" method="post" onsubmit="return confirm('Delete Post?');">
                            <input type="hidden" name="user_id" value="<?= $uploader['id'] ?>">
                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                            <br>

                            <button  class="btn btn-danger">Delete Post</button>
                        </form>
                    <?php endif ?>

                </ul>
                
            </div>
            <hr>
        </div>

        <div class="right-div container-fluid text-center justify-content-center">
            <h1><?= $post['title'] ?></h1>
            <?php
                $fileType = $post['extension']; // Assuming extension is already provided in the array

                if (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    // Display image
                    echo '<img id="post" decoding="async" src="' . '/storage/uploads/' . $post['filehash'] . '.' . $fileType . '"style="border-width: 1px;">';
                } elseif (in_array($fileType, ['mp4', 'webm', 'ogg'])) {
                    // Display video
                    echo '<video id="post" controls width="' . $post['file_width'] . '" height="' . $post['file_height'] . '">
                            <source src="' . '/storage/uploads/' . $post['filehash'] . '.' . $fileType . '" type="video/' . $fileType . '">
                            Your browser does not support the video tag.
                        </video>';
                } else {
                    echo 'Unsupported file type.';
                }
            ?>


            <div id="scalingInfo"></div>
            <select id="widthSelect">
                <option value="sample" id='sample'>Sample 850px</option>
                <option value="fitWidth">Fit Width</option>
                <option value="fitHeight">Fit Height</option>
                <option value="original">Original Size</option>
            </select>
            <br>

        
            
            <?php include '../comments/comment-form.php'; ?>
            
            <?php include '../comments/comment-view.php'; ?>
            
        </div>

        

    </div>
  
</body>

<?php include '../html-parts/footer.php'; ?>

<script>
    const widthSelect = document.getElementById('widthSelect');
    const sampleOption = document.getElementById('sample');
    const post = document.getElementById('post'); // Ensure this matches your image ID
    const scalingInfo = document.getElementById('scalingInfo');

    // Store original dimensions
    const originalWidth = <?= $post['file_width'] ?>;

    function updateScalingInfo(currentWidth) {
        const percentage = Math.min(((currentWidth / originalWidth) * 100).toFixed(0), 100);
        scalingInfo.innerHTML = `Viewing sample resized to ${percentage}% of original size.`;
        sampleOption.innerHTML = "Sample: " + post.clientWidth+ 'px';
        console.log(post.clientWidth);
    }

    widthSelect.addEventListener('change', function() {
        const option = this.value;
        console.log(option);

        switch(option) {
            case 'sample':
                post.classList.add('image-sample');
                updateScalingInfo(post.clientWidth); 
                break;
            case 'fitWidth':
                post.classList.remove('image-sample');
                post.style.height = 'auto';  
                post.style.width = '100%'; 
                post.style.objectFit = 'contain';
                scalingInfo.innerHTML = ''; 
                break;
            case 'fitHeight':
                post.classList.remove('image-sample');
                post.style.height = '100%';  
                post.style.width = 'auto';
                post.style.objectFit = 'contain';
                scalingInfo.innerHTML = '';  
                break;
            case 'original':
                post.classList.remove('image-sample');
                post.style.height = '';  
                post.style.width = ''; 
                scalingInfo.innerHTML = '';  
                break;
        }
    });

    window.onload = function() {
        widthSelect.value = 'sample';  
        widthSelect.dispatchEvent(new Event('change'));
    };

    window.onresize = function() {
        widthSelect.value = 'sample'; 
        widthSelect.dispatchEvent(new Event('change'));
    }
    
</script>

    
</html>