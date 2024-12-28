<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php'; 

if (isset($_SESSION['user_id'])) {
    
    $sql = "SELECT id, username FROM users WHERE id = {$_SESSION['user_id']}";

    $result = $mysqli->query($sql);

    $user = $result->fetch_assoc();
 

} else {
    $user = null;
}

echo '
<nav class="navbar">
    <div class="container-fluid">
    <ul class="nav nav-pills justify-content-center">
        <li class="nav-item border rounded d-flex justify-content-center align-items-center"><a class="navbar-brand justify-content-center" href="/index.php"><img src="/static/svg/site-icon.svg" height=16 width=16></a></li>
        <li class="nav-item border rounded"><a class="nav-link" href="/core/main.php">Home</a></li>';

if ($user) {
    if ($GLOBALS['_ALLOW_UPLOADS']) {
        echo '<li class="nav-item border rounded"><a class="nav-link" href="/core/posts/upload.php">Upload</a></li>';
    }

} else {
    echo '<li class="nav-item border rounded"><a class="nav-link" href="/core/users/login.php">Login</a></li>';
    if ($GLOBALS['_ALLOW_SIGNUPS']) {
        echo '<li class="nav-item border rounded"><a class="nav-link" href="/core/users/signup.php">Register</a></li>';
    }
}

echo '<li class="nav-item border rounded"><a class="nav-link" href="/core/hide.php">Hide the page! [F9]</a></li>';

if ($user) {
    echo '
    <li class="nav-item dropdown border rounded">
        <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        ' . htmlspecialchars($user['username']) . '
        </a>
        <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="/core/users/user.php?user_id=' . ($user['id']) . '">Profile</a></li> ';

        if (is_admin($user['id'])) {
            echo '<li><a class="dropdown-item" href="/ext/admin/main.php">Admin</a></li>';
        }
    echo '<li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="../core/users/logout.php">Logout</a></li>
        </ul>
    </li>';
}

if (isset($_GET["search"])) {
    $searchTerms = htmlspecialchars(trim($_GET['search']));
    $searchTerms = str_replace(' ', '+', $searchTerms);

    $searchTerms = preg_replace('/order\s*:\s*\'?(.+?)(\+|$)/', '', $searchTerms);

    $rating = '';

    if (str_contains($searchTerms, 'rating')) {
        preg_match('/rating\s*:\s*\'?(\S+?)\'?/', $searchTerms, $matches);
        
        if (isset($matches[1]) && is_numeric($matches[1])) {
            $rating = strtolower(get_rating_text($matches[1], true));
        } elseif (isset($matches[1]) && is_string($matches[1])) {
            $rating = strtolower(substr($matches[1], 0, 1));
        }
        $searchTerms = preg_replace('/rating\s*:\s*\'?(.+?)(\+|$)/', 'rating:' . $rating . ' ', $searchTerms);
    }

    $searchTerms = str_replace('+', ' ', $searchTerms);

} else {
    $searchTerms = "";
}

echo'

</ul>

<form class="d-flex dropdown position-relative" role="search" action="/core/search-posts.php" method="post">
    <input class="form-control me-2 dropdown-toggle" autocomplete="off" 
       type="search" name="search" id="searchBox" placeholder="Search" 
       value = "' . $searchTerms . '"
       aria-label="Search">


    <ul id="autocompleteBox" class="dropdown-menu " aria-labelledby="dropdownMenuButton"></ul>
 
    <button class="btn btn-outline-success" type="submit">Search</button>
</form>

</div>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const searchBox = document.querySelector("#searchBox");
        if (searchBox) {
            autocomplete(searchBox);
        }
    });
</script>
</nav>';
?>
