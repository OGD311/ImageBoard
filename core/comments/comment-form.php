<?php

if (isset($_SESSION['user_id'])) {

    echo '
<form action="../comments/make-comment.php" method="post">
    <input type="hidden" name="user_id" value="' . htmlspecialchars($user['id']) . '">
    <input type="hidden" name="post_id" value="' . htmlspecialchars($post['id']) . '">

    <input type="text" id="comment" name="comment" placeholder="Comment:" maxlength="144" value="">
    <br>
    <button type="submit">Post</button>
</form>
';

} 
?>

    
