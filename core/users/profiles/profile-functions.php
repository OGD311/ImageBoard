<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

function get_bio($user_id) {
    $mysqli = require dirname(__DIR__, 3) . "/storage/database.php";

    $sql = "SELECT bio FROM profiles WHERE user_id = ?";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $row = $result->fetch_assoc();
    $stmt->close();

    return isset($row) ? $row['bio'] : '';
}


function get_profile_picture($user_id) {
    $mysqli = require dirname(__DIR__, 3) . "/storage/database.php";

    $sql = "SELECT profile_picture FROM profiles WHERE user_id = ?";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row['profile_picture'] != null ? ($row['profile_picture']) : 'default';
}

function get_display_name($user_id) {
    $mysqli = require dirname(__DIR__, 3) . "/storage/database.php";

    $sql = "SELECT display_name FROM profiles WHERE user_id = ?";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $row = $result->fetch_assoc();
    $stmt->close();

    return isset($row) ? $row['display_name'] : get_user_name($user_id);
}
