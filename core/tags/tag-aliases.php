<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

function get_alias($tag) {
    $mysqli = require dirname(__DIR__, 2) . "/storage/database.php";

    $sql = "SELECT t.name, t.count
        FROM tags t
        WHERE t.count != 0
        AND t.id IN (
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