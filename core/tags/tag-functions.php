<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

function get_tag_id($tag) {
    if (is_numeric($tag)) {
        return $tag;
    }

    $mysqli = require dirname(__DIR__, 2) . "/storage/database.php";

    $sql = "SELECT id FROM tags WHERE name = ?";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $tag);
    $stmt->execute();
    $result = $stmt->get_result();

    $row = $result->fetch_assoc();
    $stmt->close();

    return isset($row) ? $row['id'] : null;
}

function get_tag_name($tag_id) {
    if (!is_numeric($tag_id)) {
        return $tag_id;
    }

    $mysqli = require dirname(__DIR__, 2) . "/storage/database.php";

    $sql = "SELECT name FROM tags WHERE id = ?";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $tag_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $row = $result->fetch_assoc();
    $stmt->close();

    return isset($row) ? $row['name'] : null;
}

function get_tag_count($tag) {
    $mysqli = require dirname(__DIR__, 2) . "/storage/database.php";

    if (is_numeric($tag)) {
        $sql = "SELECT count FROM tags WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("i", $tag);
    } else {
        $sql = "SELECT count FROM tags WHERE name = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $tag);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();

    $row = $result->fetch_assoc();
    $stmt->close();

    return isset($row) ? $row['count'] : 0;
}

function create_tag($tag, $count=0) {
    $mysqli = require dirname(__DIR__, 2) . "/storage/database.php";
    
    if ($count > 0) {
        $sql = "INSERT INTO tags (name, count, created_at) VALUES (?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $current_time = time();
        $stmt->bind_param("sii", $tag, $count, $current_time);
        $stmt->execute();
        $stmt->close();

        return $mysqli->insert_id;
    } else {
        $sql = "INSERT INTO tags (name, created_at) VALUES (?, ?)";

        $stmt = $mysqli->prepare($sql);
        $current_time = time();
        $stmt->bind_param("si", $tag, $current_time);
        $stmt->execute();
        $stmt->close();

        return $mysqli->insert_id;

    }
}

function recount_tags() {
    $mysqli = require dirname(__DIR__, 2) . "/storage/database.php";

    $sql = "DELETE FROM post_tags WHERE post_id NOT IN (SELECT id FROM posts);";
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->execute();
    } else {
        exit($mysqli->error);
    }

    $sql = "
        UPDATE tags
        SET count = (
            SELECT COUNT(*)
            FROM post_tags
            WHERE post_tags.tag_id = tags.id
        );
    ";
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->execute();
    } else {
        exit($mysqli->error);
    }

    $sql = "
        CREATE TEMPORARY TABLE temp_tag_counts AS
        SELECT ta.new_tag AS tag_id, t.count AS count
        FROM tags t
        JOIN tag_aliases ta ON t.id = ta.old_tag;
    ";
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->execute();
    } else {
        exit($mysqli->error);
    }

    $sql = "
        UPDATE tags
        JOIN temp_tag_counts ON tags.id = temp_tag_counts.tag_id
        SET tags.count = temp_tag_counts.count;
    ";
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->execute();
    } else {
        exit($mysqli->error);
    }

}
function get_alias($tag) {
    $mysqli = require dirname(__DIR__, 2) . "/storage/database.php";

    if (! is_numeric($tag)) {
        $sql = "SELECT t.id, t.name, t.count
            FROM tags t
            WHERE t.id IN (
                SELECT ta.new_tag
                FROM tag_aliases ta
                WHERE ta.old_tag = (
                    SELECT t.id
                    FROM tags t
                    WHERE t.name = ?
                )
            );";

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $tag);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row : null;

    } else {

        $sql = "SELECT t.id, t.name, t.count
            FROM tags t
            WHERE t.id IN (
                SELECT ta.new_tag
                FROM tag_aliases ta
                WHERE ta.old_tag = ?
            );";

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $tag);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row : null;
    }

}


function get_original($alias) {
    $mysqli = require dirname(__DIR__, 2) . "/storage/database.php";

    if (! is_numeric($alias)) {
        $sql = "SELECT t.id, t.name, t.count
            FROM tags t
            WHERE t.id IN (
                SELECT ta.old_tag
                FROM tag_aliases ta
                WHERE ta.new_tag = (
                    SELECT t.id
                    FROM tags t
                    WHERE t.name = ?
                )
            );";

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $alias);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row : null;

    } else {

        $sql = "SELECT t.id, t.name, t.count
            FROM tags t
            WHERE t.id IN (
                SELECT ta.old_tag
                FROM tag_aliases ta
                WHERE ta.new_tag = ?
            );";

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $alias);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row : null;
    }

}
