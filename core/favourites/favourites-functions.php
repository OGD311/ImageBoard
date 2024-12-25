<?php

function is_favourite($post_id, $user_id) {
    $mysqli = require dirname(__DIR__, 2) . "/storage/database.php";

    $sql = "SELECT * FROM favourites WHERE post_id = ? AND user_id = ?;";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? true : false;
}

function add_favourite($post_id, $user_id) {
    $mysqli = require dirname(__DIR__, 2) . "/storage/database.php";

    $sql = "INSERT INTO favourites (post_id, user_id) VALUES (?, ?);";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ii", htmlspecialchars($post_id), htmlspecialchars($user_id) );
    $stmt->execute();
    $stmt->close();

}

function remove_favourite($post_id, $user_id) {
    $mysqli = require dirname(__DIR__, 2) . "/storage/database.php";

    $sql = "DELETE FROM favourites WHERE post_id = ? AND user_id = ?;";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ii", htmlspecialchars($post_id), htmlspecialchars($user_id) );
    $stmt->execute();
    $stmt->close();
}