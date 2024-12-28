<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

$darkmode = isset($_COOKIE['darkmode']) ? $_COOKIE['darkmode'] : null;
if ($darkmode === 'true') {
    echo '<link rel="stylesheet" href="/static/css/main-dark.css">';
} else {
    echo '<link rel="stylesheet" href="/static/css/main-light.css">';
}
// echo '<link rel="stylesheet" href="/static/css/navbar.css">';
// echo '<link rel="stylesheet" href="/static/css/details.css">';

echo '<script src="/static/js/search-bar.js" type="module"></script>';
echo '<script src="/static/js/hide-page.js"></script>';
echo '<script src="/static/js/cookie.js" type="module"></script>';

if (! $GLOBALS['_SITE_DOWNTIME']) {
    echo '<script src="/static/js/check-age.js" type="module"></script>';

} elseif (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) !== '/downtime.php') {
    
    echo '<script src="/static/js/site-downtime.js" type="module"></script>';
    header('Location: /downtime.php');
    exit();
}

echo '<script src="/static/js/favourite-toggle.js" type="module"></script>';
echo '<script src="/static/js/autocomplete-fill.js" type="module" defer></script>';
