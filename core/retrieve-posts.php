<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

function get_posts($search = [], $page = 1, $count = false) {
    // Ensure the page is at least 1
    $page = max(1, $page);

    // Setup defaults
    $order_by = 'uploaded_at DESC';
    $joinTags = false;
    $mysqli = require __DIR__ . "../../storage/database.php";
    $_POSTS_PER_PAGE = $GLOBALS['_POSTS_PER_PAGE'];

    // Base SQL query
    $sql = "SELECT DISTINCT p.* FROM posts p";  // Use DISTINCT to avoid duplicates
    $conditions = [];
    $includeTags = [];
    $excludeTags = [];

    foreach (array_filter($search) as $key => $term) {
        $negation = false;

        // Handle negation (tags that should be excluded)
        var_dump($term);
        if (strpos($term, '-') === 0) {
            $negation = true;
            $term = ltrim($term, '-');
        }

        // Skip empty search terms
        if (empty($term)) {
            continue;
        }

        var_dump($negation);

        // Handle special cases: rating, title, user, and order
        if (preg_match('/rating\s*:\s*\'?(\S+?)\'?/', $term, $matches)) {
            $conditions[] = "p.rating " . ($negation === true ? 'NOT ' : '') . " LIKE '" . $mysqli->real_escape_string(get_rating_value(substr($matches[1], 0))) . "'";
        
        } elseif (preg_match('/title\s*:\s*\'?(.+?)(\+|$)/', $term, $matches)) {
            $conditions[] = "p.title " . ($negation === true ? 'NOT ' : '') . " LIKE '" . $mysqli->real_escape_string($matches[1]) . "'";
        
        } elseif (preg_match('/user\s*:\s*\'?(.+?)(\+|$)/', $term, $matches)) {
            $conditions[] = "p.user_id " . ($negation === true ? 'NOT ' : '') . " LIKE '" . $mysqli->real_escape_string(get_user_id($matches[1])) . "'";
        
        } elseif (preg_match('/order\s*:\s*\'?(.+?)(\+|$)/', $term, $matches)) {
            $order_by = $mysqli->real_escape_string($matches[1]);
        
        } elseif (preg_match('/ext\s*:\s*\'?(.+?)(\+|$)/', $term, $matches)) {
            $conditions[] = "p.extension " . ($negation === true ? 'NOT ' : '') . "LIKE '" . $mysqli->real_escape_string($matches[1]) . "'";
        
        } elseif (preg_match('/height\s*:\s*\'?(.+?)(\+|$)/', $term, $matches)) {
            $conditions[] = "p.file_height " . ($negation === true ? 'NOT ' : '') . " LIKE '" . $mysqli->real_escape_string($matches[1]) . "'";
        
        } elseif (preg_match('/width\s*:\s*\'?(.+?)(\+|$)/', $term, $matches)) {
            $conditions[] = "p.file_width " . ($negation === true ? 'NOT ' : '') . " LIKE '" . $mysqli->real_escape_string($matches[1]) . "'";
        
        } else {
            // Handle tags
            $joinTags = true;
            if ($negation) {
                $excludeTags[] = $mysqli->real_escape_string(trim($term));
            } else {
                $includeTags[] = $mysqli->real_escape_string(trim($term));
            }
        }
    }

    var_dump($conditions);

    // Join post_tags and tags tables if tags are involved
    if ($joinTags) {
        $sql .= " LEFT JOIN post_tags pt ON p.id = pt.post_id 
                  LEFT JOIN tags t ON pt.tag_id = t.id";
    }

    // Build WHERE clause with conditions and tag filtering
    $whereClauses = array_filter($conditions);
    
    if (!empty($includeTags)) {
        $sql .= " WHERE p.id IN (
                    SELECT pt.post_id 
                    FROM post_tags pt 
                    JOIN tags t ON pt.tag_id = t.id 
                    WHERE t.name IN ('" . implode("', '", $includeTags) . "')
                    GROUP BY pt.post_id
                    HAVING COUNT(DISTINCT t.name) = " . count($includeTags) . "
                  )";
    }

    if (!empty($excludeTags)) {
        // Ensure posts that either do not have the excluded tags or have no tags at all
        $sql .= (empty($includeTags) ? " WHERE " : " AND ") . "(
                    p.id NOT IN (
                        SELECT pt.post_id 
                        FROM post_tags pt 
                        JOIN tags t ON pt.tag_id = t.id 
                        WHERE t.name IN ('" . implode("', '", $excludeTags) . "')
                    )
                    OR p.id NOT IN (SELECT post_id FROM post_tags)
                  )";
    }

    // Append other conditions (e.g., rating, title, user)
    if (!empty($whereClauses)) {
        $sql .= (empty($includeTags) && empty($excludeTags) ? " WHERE " : " AND ") . implode(' AND ', $whereClauses);
    }

    // Order and pagination
    $sql .= " ORDER BY " . get_order_sql($order_by) . " 
              LIMIT " . $_POSTS_PER_PAGE . " 
              OFFSET " . (($page - 1) * $_POSTS_PER_PAGE) . ";";

    // Execute the query and fetch posts
    $result = $mysqli->query($sql);
    if (!$result) {
        return [];
    }

    $posts = [];
    while ($post = $result->fetch_assoc()) {
        $posts[] = $post;
    }

    // If count is requested, fetch the total number of posts
    if ($count) {
        $count_sql = "SELECT COUNT(DISTINCT p.id) AS total_posts FROM posts p";

        if ($joinTags) {
            $count_sql .= " LEFT JOIN post_tags pt ON p.id = pt.post_id 
                            LEFT JOIN tags t ON pt.tag_id = t.id";
        }

        $whereClauses = array_filter($conditions);
    
        if (!empty($includeTags)) {
            $count_sql .= " WHERE p.id IN (
                        SELECT pt.post_id 
                        FROM post_tags pt 
                        JOIN tags t ON pt.tag_id = t.id 
                        WHERE t.name IN ('" . implode("', '", $includeTags) . "')
                        GROUP BY pt.post_id
                        HAVING COUNT(DISTINCT t.name) = " . count($includeTags) . "
                    )";
        }

        if (!empty($excludeTags)) {
            // Ensure posts that either do not have the excluded tags or have no tags at all
            $count_sql .= (empty($includeTags) ? " WHERE " : " AND ") . "(
                        p.id NOT IN (
                            SELECT pt.post_id 
                            FROM post_tags pt 
                            JOIN tags t ON pt.tag_id = t.id 
                            WHERE t.name IN ('" . implode("', '", $excludeTags) . "')
                        )
                        OR p.id NOT IN (SELECT post_id FROM post_tags)
                    )";
        }

        // Append other conditions (e.g., rating, title, user)
        if (!empty($whereClauses)) {
            $count_sql .= (empty($includeTags) && empty($excludeTags) ? " WHERE " : " AND ") . implode(' AND ', $whereClauses);
        }

        $count_result = $mysqli->query($count_sql);
        $total_posts = $count_result->fetch_assoc()['total_posts'];

        $mysqli->close();

        return [
            'posts' => $posts,
            'total_posts' => $total_posts,
        ];
    }

    $mysqli->close();
    return $posts;
}

function get_order_sql($order_by) {
    $order_map = [
        'upload-asc' => 'uploaded_at ASC',
        'upload-desc' => 'uploaded_at DESC',
        'updated-asc' => 'updated_at ASC',
        'updated-desc' => 'updated_at DESC',
        'comments-asc' => 'comment_count ASC',
        'comments-desc' => 'comment_count DESC',
    ];

    return $order_map[$order_by] ?? 'uploaded_at DESC';
}
