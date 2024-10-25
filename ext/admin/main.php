<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

session_start();
 
if (!isset($_SESSION['user_id']) || !is_admin($_SESSION['user_id'])) {
    header('Location: /core/users/login.php');
    exit();
}
 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $config_file = $_SERVER['DOCUMENT_ROOT'] . '/config.php';
    $config_content = file_get_contents($config_file);

    if ($config_content === false) {
        die("Failed to read config file.");
    }


    $allow_uploads = isset($_POST['allow_uploads']) ? 'true' : 'false';
    $allow_signups = isset($_POST['allow_signups']) ? 'true' : 'false';
    $site_downtime = isset($_POST['site_downtime']) ? 'true' : 'false';
    $site_downtime_message = isset($_POST['site_downtime_message']) ? $_POST['site_downtime_message'] : '';


    $config_content = preg_replace(
        "/_ALLOW_UPLOADS'\]\s*=\s*(true|false);/",
        "_ALLOW_UPLOADS'] = $allow_uploads;",
        $config_content
    );

    $config_content = preg_replace(
        "/_ALLOW_SIGNUPS'\]\s*=\s*(true|false);/",
        "_ALLOW_SIGNUPS'] = $allow_signups;",
        $config_content
    );

    $config_content = preg_replace(
        "/_SITE_DOWNTIME'\]\s*=\s*(true|false);/",
        "_SITE_DOWNTIME'] = $site_downtime;",
        $config_content
    );

    $site_downtime_message = addslashes($site_downtime_message); 
    $config_content = preg_replace(
        "/_SITE_DOWNTIME_MESSAGE'\]\s*=\s*'(.*?)';/",
        "_SITE_DOWNTIME_MESSAGE'] = '$site_downtime_message';",
        $config_content
    );


    file_put_contents($config_file, $config_content);
    

    header('Location: main.php');
    exit();

}
    
    $allow_uploads = $GLOBALS['_ALLOW_UPLOADS'];
    $allow_signups = $GLOBALS['_ALLOW_SIGNUPS'];
    $site_downtime = $GLOBALS['_SITE_DOWNTIME'];
    $site_downtime_message = $GLOBALS['_SITE_DOWNTIME_MESSAGE'];


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
</head>
<body>
    <h1>Admin Console</h1>
    <h3>Welcome <?= htmlspecialchars(get_user_name($_SESSION['user_id'])) ?></h3>
    <ul>
        <li><a href="/index.php">Home</a></li>
        <li><a href="regenerate-thumbnails.php">Regenerate Thumbnails</a></li>
        <li><a href="recount-tags.php">Recount Tags</a></li>
        <li><a href="sql-test.php" onclick="return confirmWarning();">SQL Test</a></li>
        <li><a href="shuffle-ids.php">Shuffle IDs</a></li>
        <br>
        <li>
            <form method="post" action="main.php">
                <input type="checkbox" name="allow_uploads" id="allow_uploads" <?= ($allow_uploads == 'true') ? 'checked' : '' ?>>
                <label for="allow_uploads">Allow Uploads</label>
                <br>
                <input type="checkbox" name="allow_signups" id="allow_signups" <?= ($allow_signups == 'true') ? 'checked' : '' ?>>
                <label for="allow_signups">Allow Signups</label>
                <br>
                <input type="checkbox" name="site_downtime" id="site_downtime" <?= ($site_downtime == 'true') ? 'checked' : '' ?>>
                <label for="site_downtime">Site Downtime?</label>
                <br>
                <input type="text" placeholder="Downtime message" name="site_downtime_message" id="site_downtime_message" value='<?=  htmlspecialchars($site_downtime_message) ?>'>
                <br>
                <input type="submit" value="Submit">
            </form>
        </li>
    </ul>
</body>

<script>
    function confirmWarning() {
        return confirm("Warning: You are about to run a SQL test.This will add about 40000 entries to the DB. Do you wish to continue?");
    }
</script>
</html>
