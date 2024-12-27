<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once 'convert-pictures.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /core/users/login.php');
    exit();
}

$mysqli = $_DB;

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $user_id = $_SESSION['user_id'];

    $stmt = $mysqli->prepare("SELECT display_name, bio, profile_picture FROM profiles WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($current_display_name, $current_bio, $current_profile_picture);
    $stmt->fetch();
    $stmt->close();
    
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_POST['user_id'];
    
    // Fetch current profile data
    $stmt = $mysqli->prepare("SELECT display_name, bio, profile_picture FROM profiles WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($current_display_name, $current_bio, $current_profile_picture);
    $stmt->fetch();
    $stmt->close();
    
    // Use current values if the new ones are blank
    $bio = !empty($_POST['bio']) ? $_POST['bio'] : $current_bio;
    $display_name = !empty($_POST['display_name']) ? $_POST['display_name'] : $current_display_name;
    $profile_picture = null;
    $remove_picture = !empty($_POST['remove_picture']) ? $_POST['remove_picture'] : null;

    if (!empty($_FILES) && $_FILES["profile_picture"]["error"] === UPLOAD_ERR_OK) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($_FILES["profile_picture"]["tmp_name"]);

        $allowed_mime_types = ["image/png", "image/jpeg", "image/jpg"];
        if (!in_array($mime_type, $allowed_mime_types)) {
            exit("Invalid file type");
        }

        if ($_FILES["profile_picture"]["size"] > 5000000) { // 5MB limit
            exit("File too large (max 5MB)");
        }

        $pathinfo = pathinfo($_FILES["profile_picture"]["name"]);
        $pathinfo['extension'] = strtolower($pathinfo['extension']);

        if ($pathinfo['extension'] == 'jpeg') {
            $pathinfo['extension'] = 'jpg';
        }

        $base = $pathinfo["filename"];
        $base = preg_replace("/[^\w-]/", "_", $base);
        $filename = $base . "." . $pathinfo['extension'];
        $filehash = md5($_FILES["profile_picture"]["name"]);
        $destination = $_PROFILEPICTUREPATH . $filehash . "." . strtolower($pathinfo['extension']);

        convert_and_store($_FILES["profile_picture"]["tmp_name"], $destination);
        $profile_picture = $filehash;
    }

    $sql = "UPDATE profiles SET display_name = ?, bio = ?";
    if ($profile_picture) {
        $sql .= ", profile_picture = ?";
    }
    if ($remove_picture) {
        $sql .= ", profile_picture = NULL";
    }
    $sql .= " WHERE user_id = ?";

    $stmt = $mysqli->stmt_init();
    if (!$stmt->prepare($sql)) {
        die("SQL Error " . $mysqli->error);
    }

    if ($profile_picture) {
        $stmt->bind_param('sssi', $display_name, $bio, $profile_picture, $user_id);
    } else {
        $stmt->bind_param('ssi', $display_name, $bio, $user_id);
    }

    $stmt->execute();

    $mysqli->close();
    header('Location: /core/users/user.php?user_id=' . $user_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Profile Picture</title>
    <?php include '../../html-parts/header-elems.php'; ?>
</head>
<body>
    <?php include '../../html-parts/nav.php'; ?>

    <h1>Edit Profile</h1>

    <form action="edit-profile.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
        <label for="display_name">Display Name</label><br>
        <input type="text" id="display_name" name="display_name" value="<?= htmlspecialchars($current_display_name) ?>"><br>
        
        <label for="bio">Bio</label><br>
        <textarea id="bio" name="bio" rows="4" cols="50"><?= htmlspecialchars($current_bio) ?></textarea><br>
        
        <label for="profile_picture">Profile Picture (PNG, JPEG)</label><br>
        <input type="file" id="profile_picture" name="profile_picture" accept="image/png, image/jpeg, image/jpg" onchange="loadFile(event);"><br>
        
        <button type="submit">Save</button>
    </form>

    <form action="edit-profile.php" method="post">
        <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
        <input type="hidden" name="remove_picture" value="1">
        <button type="submit">Remove Profile Picture</button>
    </form>

    <canvas id="output" style="object-fit: contain;"></canvas>

    <?php include '../../html-parts/footer.php'; ?>
</body>

<script>
    window.onload = function() {
    var profilePicture = "<?= $current_profile_picture ?>";
    if (!profilePicture) {
        profilePicture = "default";
    }
    var output = document.getElementById('output');
    output.width = 400;
    output.height = 400;

    var filePath = '/storage/profilepictures/' + profilePicture + '.jpg';
    var ctx = output.getContext('2d');

    var img = new Image();
    img.onload = function() {
        ctx.clearRect(0, 0, output.width, output.height); // Clear canvas
        ctx.drawImage(img, 0, 0, output.width, output.height);
    };
    img.src = filePath;
};

    var loadFile = function(event) {
        var output = document.getElementById('output');
        output.width = 400;
        output.height = 400;

        var file = event.target.files[0];
        var ctx = output.getContext('2d');
        var url = URL.createObjectURL(file);

        var img = new Image();
        img.onload = function() {
            ctx.clearRect(0, 0, output.width, output.height); // Clear canvas
            ctx.drawImage(img, 0, 0, output.width, output.height);
            URL.revokeObjectURL(img.src);
        };
        img.src = url;
        
    };
</script>
</html>