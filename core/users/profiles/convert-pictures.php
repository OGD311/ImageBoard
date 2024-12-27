<?php 

use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode; // Correct import for TimeCode

function convert_and_store($source, $destination) { 

    $maxWidth = 800;
    $maxHeight = 800;
    $quality = 75;

    $info = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $info->file($source);

    if ($info === false) {
        die("Error: Invalid image file.");
    }

    if (!is_dir(dirname($destination))) {
        die("Error: Destination folder does not exist.");
    }

    // Handling images
    if ($mime_type == 'image/jpeg') {
        $image = imagecreatefromjpeg($source);
    } elseif ($mime_type == 'image/png') {
        $image = imagecreatefrompng($source);
    } else {
        die("Error: Unsupported image type.");
    }

    // Processing images
    $width = imagesx($image);
    $height = imagesy($image);

    $aspectRatio = $width / $height;
    if ($width > $maxWidth || $height > $maxHeight) {
        if ($aspectRatio > 1) {
            $newWidth = $maxWidth;
            $newHeight = $maxWidth / $aspectRatio;
        } else {
            $newHeight = $maxHeight;
            $newWidth = $maxHeight * $aspectRatio;
        }
    } else {
        $newWidth = $width;
        $newHeight = $height;
    }

    $newWidth = round($newWidth);
    $newHeight = round($newHeight);

    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    if ($mime_type == 'image/png') {
        // Convert PNG to JPEG
        $background = imagecolorallocate($newImage, 255, 255, 255); // white background
        imagefill($newImage, 0, 0, $background);
    }

    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    if (!imagejpeg($newImage, $destination, $quality)) {
        die("Error: Failed to write image to destination. Check permissions and path.");
    }

    imagedestroy($image);
    imagedestroy($newImage);

    return $destination;
}
?>