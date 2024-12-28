<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../../main.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Signup</title>
        <script src="https:unpkg.com/just-validate@latest/dist/just-validate.production.min.js" defer></script>
        <script src="../static/js/validate.js" defer></script>
        <meta charset="UTF-8">
        <?php include '../html-parts/header-elems.php' ?>        
    </head>

    <body>
        <?php include '../html-parts/nav.php'; ?>


        <h1>Signup</h1>

        <?php

            if ($GLOBALS['_ALLOW_UPLOADS']){
                echo '
                <form action="process-signup.php"   method="post" id="signup">
                    <div>
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username">
                    </div>

                    <div>
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password">
                    </div>

                    <div>
                        <label for="password_confirmation">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation">
                    </div>

                    <button>Sign up</button>
                </form>';
            
            } else {
                echo '<p>Signups are closed at this time</p>';
            }

        ?>
            <p>Have an account? <a href="users/login.php">Login</a></p>

        
    </body>

    
    <?php include '../html-parts/footer.php'; ?>
</html>