<?php
use Dotenv\Dotenv;

// File Links 
$GLOBALS['_DB'] = require __DIR__ . "/storage/database.php";

$GLOBALS['_UPLOADPATH'] = __DIR__ . "/storage/uploads/";
$GLOBALS['_THUMBNAILPATH'] = __DIR__ . "/storage/thumbnails/";
$GLOBALS['_PROFILEPICTUREPATH'] = __DIR__ . "/storage/profilepictures/";



// Admin Controls
$GLOBALS['_ALLOW_UPLOADS'] = true;
$GLOBALS['_ALLOW_SIGNUPS'] = true;
$GLOBALS['_SITE_DOWNTIME'] = false;
$GLOBALS['_SITE_DOWNTIME_MESSAGE'] = '';

$GLOBALS['_POSTS_PER_PAGE'] = 60;
$GLOBALS['_TAGS_ALL_LIMIT'] = 16;
$GLOBALS['_COMMENTS_PER_PAGE'] = 16;
$GLOBALS['_COMMENT_CHARACTER_LIMIT'] = 256;

// Redis
use Predis\Client as Redis;

$redis = new Redis([
    'host' => $_ENV['REDIS_HOST'],
    'port' => $_ENV['REDIS_PORT'],
]);
$pubsub = new Redis([
    'host' => $_ENV['REDIS_HOST'],
    'port' => $_ENV['REDIS_PORT'],
]);
$GLOBALS['_REDIS'] = $redis;
$GLOBALS['_PUBSUB'] = $pubsub;
$GLOBALS['_REDISTIMEOUT'] = 300;

// General functions

function is_admin($user_id) {
    $mysqli = require __DIR__ . "/storage/database.php";

    $stmt = $mysqli->prepare("SELECT is_admin FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id); 
    $stmt->execute();

    $result = $stmt->get_result();
    $is_admin = $result->fetch_assoc()['is_admin'];
    $stmt->close();
    $mysqli->close();
    
    $val = isset($is_admin) ? (bool)$is_admin : false;

    return $val;
}

function post_title($post_id) {
    $mysqli = require __DIR__ . "/storage/database.php";

    $stmt = $mysqli->prepare("SELECT title FROM posts WHERE id = ?");
    $stmt->bind_param("i", $post_id); 
    $stmt->execute();

    $result = $stmt->get_result();
    $title = $result->fetch_assoc()['title'];
    $stmt->close();
    $mysqli->close();

    return $title;
}

function get_user_id($username) {
    $mysqli = require __DIR__ . "/storage/database.php";

    if (is_numeric($username)) {
        return $username;
    } 

    $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();
    try {
        $user = $result->fetch_assoc();
        if ($user) {
            $user_id = $user['id'];
        } else {
            $user_id = null;
        }
    } finally {
        $stmt->close();
        $mysqli->close();
    }


    return isset($user_id) ? $user_id : '';
}

function get_user_name($user_id) {
    $mysqli = require __DIR__ . "/storage/database.php";

    if (!is_numeric($user_id)) {
        return $user_id;
    }
    if ($user_id < 0) {
        return '';
    }

    $stmt = $mysqli->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $username = $result->fetch_assoc()['username'];
    $stmt->close();
    $mysqli->close();

    return isset($username) ? $username : '';
}


function total_posts_count() {
    $mysqli = require __DIR__ . "/storage/database.php";

    $sql = "SELECT COUNT(*) AS total_posts FROM posts";

    $stmt = $mysqli->prepare($sql);

    $stmt->execute();
    $result = $stmt->get_result();
    $posts_count = $result->fetch_assoc();

    $mysqli->close();

    return $posts_count['total_posts'];
}


function number_of_pages($posts_count) {
    $posts_per_page = $GLOBALS['_POSTS_PER_PAGE'];

    $number_of_pages = intval(ceil($posts_count / $posts_per_page));

    return $number_of_pages;
}

function get_rating_text($rating_value, $short=false) {
    switch ($rating_value) {
        case 0: 
            if ($short) {
                return 'S';
            }
            return 'Safe';
        
        case 1:
            if ($short) {
                return 'Q';
            }
            return 'Questionable';

        case 2:
            if ($short) {
                return 'E';
            }
            return 'Explicit';

        default:
            if ($short) {
                return 'E';
            }
            return 'Explicit';
    }
}

function get_rating_value($rating_text) {
    switch (strtolower(substr($rating_text, 0, 1))) {
        case 's': 
            return 0;
        
        case 'q':
            return 1;

        case 'e':
            return 2;

        default:
            return 2;
    }
}