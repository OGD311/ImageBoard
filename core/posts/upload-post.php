<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require 'compress-image.php';


require '../../vendor/autoload.php';
use FFMpeg\FFMpeg;

if (! $GLOBALS['_ALLOW_UPLOADS']) {
    header('Location: upload.php');
    exit();
}
 

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header('Location: upload.php');
    exit();
}

if (empty($_FILES)) {
    exit('$_FILES is empty - is file_uploads enabled in php.ini?');
}

if ($_FILES["media"]["error"] !== UPLOAD_ERR_OK) {

    switch ($_FILES["media"]["error"]) {
        case UPLOAD_ERR_PARTIAL:
            exit("File only partially uploaded");
            break;
        case UPLOAD_ERR_NO_FILE:
            exit("No file was uploaded");
            break;
        case UPLOAD_ERR_EXTENSION:
            exit("File upload stopped by a PHP extension");
            break;
        case UPLOAD_ERR_FORM_SIZE:
            exit("File exceeds MAX_FILE_SIZE");
            break;
        case UPLOAD_ERR_INI_SIZE:
            exit("File exceeds upload_max_filesize in php.ini");
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            exit("Temporary folder not found");
            break;
        case UPLOAD_ERR_CANT_WRITE:
            exit("Failed to write file");
            break;
        default:
            exit("Upload error");
            break;

    }

}


if ($_FILES["media"]["size"] > 20000000 ) {
    exit("File too large (max 20MB)");
}

$finfo = new finfo(FILEINFO_MIME_TYPE);

$mime_type = $finfo->file($_FILES["media"]["tmp_name"]);


$mime_types = [
    "image/gif",
    "image/png",
    "image/jpeg",
    "image/webp",
    "video/webm",
    "video/mp4",
    "video/x-msvideo",
    "video/mpeg",
    "video/quicktime",
    "video/x-flv",
    "video/x-matroska"
];


if ( ! in_array($mime_type, $mime_types)) {
    exit("Invalid file type");
} 

// Check if the file is a video
if (strpos($mime_type, 'video/') === 0) {
    // Use PHP-FFMpeg to get details for video files
    $ffmpeg = FFMpeg::create();
    $mediaFile = $_FILES["media"]["tmp_name"];
    
    // Open the video file
    $video = $ffmpeg->open($mediaFile);
    
    // Get video information
    $format = $video->getFormat();
    $dimensions = $video->getStreams()->first()->getDimensions();
    
    var_dump($dimensions);

    $file_width = $dimensions->getWidth();
    $file_height = $dimensions->getHeight();
    $type = $format->get('format_name'); // Gets the format type, e.g. 'mp4'
    $attr = ''; // Placeholder for attributes
    
} else {
    // Use getimagesize for non-video files (images)
    list($file_width, $file_height, $type, $attr) = getimagesize($_FILES["media"]["tmp_name"]);
}

// Create safe path and hash for file
$pathinfo = pathinfo($_FILES["media"]["name"]);
$pathinfo['extension'] = strtolower($pathinfo['extension']);

if ($pathinfo['extension'] == 'jpeg') {
    $pathinfo['extension'] = 'jpg';
}

$base = $pathinfo["filename"];

$base = preg_replace("/[^\w-]/", "_", $base);


$filename = $base . "." . $pathinfo['extension'];

$filehash = md5($_FILES["media"]["name"]);

$destination = $_UPLOADPATH . $filehash . "." . strtolower($pathinfo['extension']);

if ( ! move_uploaded_file($_FILES["media"]["tmp_name"], $destination)) {
    exit("Can't move uploaded file");
}

// Other variables needed for SQL upload
$title = $_POST['title'];

$user_id = $_POST['user_id'];

$extension = strtolower($pathinfo['extension']);

$filesize = $_FILES["media"]["size"];

$rating = $_POST['rating'];

if (! is_numeric($rating) && $rating >= 0 && $rating <= 2) {
    die('Please enter a valid rating');
}

$uploaded_at = time();

// Upload to SQL

$mysqli = $_DB;

$sql = "INSERT INTO posts (title, user_id, extension, filesize, filehash, file_height, file_width, rating, uploaded_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $mysqli->stmt_init();

if (! $stmt->prepare($sql)) {
    die("SQL Error " . $mysqli->error);
}

$stmt->bind_param('sisisiiis' , $title, $user_id, $extension, $filesize, $filehash, $file_height, $file_width, $rating, $uploaded_at);

$stmt->execute();

compress($destination, $_THUMBNAILPATH . $filehash . "-thumb.jpg");

// Redirect to new post

$sql = sprintf("SELECT id FROM posts WHERE filehash = '%s' AND uploaded_at = '%s'", $filehash, $uploaded_at);

$result = $mysqli->query($sql);

$post_id = ($result->fetch_assoc())['id'];

$mysqli->close();
header('Location: /core/posts/view.php?post_id=' . $post_id);
exit();