<?php 
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home</title>
    <?php include 'core/html-parts/header-elems.php' ?>


</head>

<body>

    <div style="height: 100vh;">
        <div>
            <p>You must be over the age of 18 and agree to the <a href="#" >terms and conditions</a> to access this page.</p>
            <button id="agreeBtn" >
                I agree to the <a href="#" >terms and conditions</a>
            </button>
            <button id="disagreeBtn" >
                I do not agree to the <a href="#" >terms and conditions</a>
            </button>
        </div>
    </div>
   


    <script type="module">
        import { setCookie } from '/static/js/cookie.js'; 

        window.agree = function() {
            setCookie('ageCheck', 'agree'); 
            window.location.href = 'index.php';
        };

        window.disagree = function() {
            setCookie('ageCheck', 'disagree'); 
            window.location.replace('https:www.google.com');
        };

        document.getElementById('agreeBtn').onclick = agree;
        document.getElementById('disagreeBtn').onclick = disagree;
    </script>


<?php include 'core/html-parts/footer.php'; ?>
</body>
</html>
