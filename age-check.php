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

    <div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
        <div class="border border-black p-4 text-center">
            <p>You must be over the age of 18 and agree to the <a href="#" class="text-blue">terms and conditions</a> to access this page.</p>
            <button id="agreeBtn" class="btn btn-success ">
                I agree to the <a href="#" class="text-white">terms and conditions</a>
            </button>
            <button id="disagreeBtn" class="btn btn-secondary">
                I do not agree to the <a href="#" class="text-white">terms and conditions</a>
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
