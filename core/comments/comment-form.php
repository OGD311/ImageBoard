<?php

if (isset($_SESSION['user_id'])) {

    echo '
<form action="../comments/make-comment.php" method="post" onsubmit="document.getElementById("comment").value = ""; document.getElementById("submit").disabled=true;">
    <input type="hidden" name="user_id" value="' . htmlspecialchars($user['id']) . '">
    <input type="hidden" name="post_id" value="' . htmlspecialchars($post['id']) . '">

    <input type="text" id="comment" name="comment" 
    placeholder="Comment:" required maxlength="' . $GLOBALS['_COMMENT_CHARACTER_LIMIT'] . '" value="">
    <br>
    <button type="submit" id="submit">Post</button>
</form>

<script>
    window.onload = document.getElementById("comment").value = "";
</script>
';

} 
?>

    
