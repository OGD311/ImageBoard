<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require '../../core/posts/compress-image.php';

session_start();

if (! isset($_SESSION['user_id']) && !is_admin($_SESSION['user_id'])) {
    header('Location: /core/users/login.php');
    exit();
}


$contents = scandir($_UPLOADPATH);

foreach ($contents as $item) {
    if ($item != '.' && $item != '..') {
        $imagePath = $_UPLOADPATH . $item;

        // Only match filenames with a 32-character MD5 hash and a valid image extension (jpg, png)
        if (preg_match('/([a-f0-9]{32})\.(jpg|jpeg|png|gif)$/', $item, $matches)) {
            $filehash = $matches[1];

            try {
                compress($imagePath, $_THUMBNAILPATH . $filehash . "-thumb.jpg");
            } catch (Exception $e) {
                echo "Error compressing image: " . $e->getMessage() . "\n";
            }
        } else {
            // Log when a file does not match the expected pattern
            echo "File skipped (not matching MD5 hash pattern or unsupported extension): $item\n";
        }
    }

}


header('Location: main.php');
exit();