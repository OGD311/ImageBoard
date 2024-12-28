<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/tags/tag-functions.php';

function reset_tags($post_id) {
    $mysqli = require __DIR__ . "/../../storage/database.php";

    $sql = "DELETE FROM post_tags WHERE post_id = ?";
    $stmt = $mysqli->stmt_init();
    $stmt->prepare($sql);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $stmt->close();
}

function post_tags($post_id, $tags) {
    $mysqli = require __DIR__ . "/../../storage/database.php";

    $tags = explode(' ', $tags);

    foreach (array_filter($tags) as $key => $term) {
        // Handle negation 
        if (strpos($term, '-') === 0) {
            continue;
        }

        // Skip empty search terms
        if (empty($term)) {
            continue;
        }
        // Search by rating
        if (preg_match('/rating\s*:\s*\'?(\S+?)\'?/', $term, $matches)) {
            continue;
        // Search by title
        } elseif (preg_match('/title\s*:\s*\'?(.+?)(\+|$)/', $term, $matches)) {
            continue;
        
        // Search by username
        } elseif (preg_match('/user\s*:\s*\'?(.+?)(\+|$)/', $term, $matches)) {
            
            continue;
        
        // Search by order
        } elseif (preg_match('/order\s*:\s*\'?(.+?)(\+|$)/', $term, $matches)) {
            continue;
        
        // Search by file extension
        } elseif (preg_match('/ext\s*:\s*\'?(.+?)(\+|$)/', $term, $matches)) {
            continue;
        
        } elseif (preg_match('/filetype\s*:\s*\'?(.+?)(\+|$)/', $term, $matches)) {
            continue;
            // Search by height 
        } elseif (preg_match('/height\s*:\s*\'?(.+?)(\+|$)/', $term, $matches)) {
            continue;
        // Search by width
        } elseif (preg_match('/width\s*:\s*\'?(.+?)(\+|$)/', $term, $matches)) {
            continue;

        } else {
            // Handle tags
            $tag = get_tag_id($term);
            if (!$tag) {
                $tag = create_tag($term);
            }
            $sql = "INSERT INTO post_tags (post_id, tag_id) SELECT ?, ? FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM post_tags WHERE post_id = ? AND tag_id = ?)";
            $stmt = $mysqli->stmt_init();
            $stmt->prepare($sql);
            $stmt->bind_param("iiii", $post_id, $tag, $post_id, $tag);
            $stmt->execute();
            $stmt->close();

        }
    }
}

?>