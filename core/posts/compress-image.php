<?php 
require '../../vendor/autoload.php';

use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use FFMpeg\Coordinate\Dimension;

function compress($source, $destination) { 

    $maxWidth = 800;
    $maxHeight = 640;
    $quality = 50;

    $info = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $info->file($source);


    if ($info === false) {
        die("Error: Invalid image file.");
    }

    if (!is_dir(dirname($destination))) {
        die("Error: Destination folder does not exist.");
    }

    // Handling images
    if ($info['mime'] == 'image/jpeg') {
        $image = imagecreatefromjpeg($source);
    } elseif ($info['mime'] == 'image/gif') {
        $image = imagecreatefromgif($source);
    } elseif ($info['mime'] == 'image/png') {
        $image = imagecreatefrompng($source);
    } elseif (strpos($info['mime'], 'video/') === 0) { // Check if the MIME type is video
        // Extract the first frame as a JPEG
        $ffmpeg = FFMpeg::create();
        $video = $ffmpeg->open($source);
        
        // Extract the first frame
        $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(0))->save($destination);
        
        return $destination; // Return early for video
    } else {
        die("Error: Unsupported image/video type.");
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
 
    if ($info['mime'] == 'image/png' || $info['mime'] == 'image/gif') {
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
        imagefill($newImage, 0, 0, $transparent);
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
