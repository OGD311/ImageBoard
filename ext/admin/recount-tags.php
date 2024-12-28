<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/tags/tag-functions.php';

session_start();

$mysqli = $_DB;

if (!isset($_SESSION['user_id']) || !is_admin($_SESSION['user_id'])) {
    header('Location: /core/users/login.php');
    exit();
}

recount_tags();

// Redirect to main page
header('Location: main.php');
exit();
