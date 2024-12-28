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
