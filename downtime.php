<?php 
require_once 'config.php';

if (! $GLOBALS['_SITE_DOWNTIME']) {
    header('Location: index.php');
}
 
if (isset($GLOBALS['_SITE_DOWNTIME_MESSAGE'])) {
    $siteDownMessage = $GLOBALS['_SITE_DOWNTIME_MESSAGE'];
} else {
    $siteDownMessage = '';
}


?>



<!DOCTYPE html>
<html>
    <head>
        <title>Downtime</title>
        <?php include 'core/html-parts/header-elems.php' ?>
        <meta charset="UTF-8">
    </head>

    <body>

        <div>
            <h1>Site is down</h1>
            <h3>Check back later</h3>

            <?php
                if ($siteDownMessage) {
                    echo '<h4>' . $siteDownMessage . '</h4>';
                }
    
            ?>
        <div>


    </body>
</html>