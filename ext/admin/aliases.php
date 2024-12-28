<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/tags/tag-functions.php';

session_start();

$mysqli = $_DB;

if (!isset($_SESSION['user_id']) || !is_admin($_SESSION['user_id'])) {
    header('Location: /core/users/login.php');
    exit();
}

$result = null;

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $query = "SELECT ta.new_tag, ta.old_tag, t1.name as new_tag_name, t2.name as old_tag_name 
              FROM tag_aliases ta
              INNER JOIN tags t1 ON ta.new_tag = t1.id
              INNER JOIN tags t2 ON ta.old_tag = t2.id";
    $result = $mysqli->query($query);


}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $delete_id = isset($_POST['delete_id']) ? explode("_", $_POST['delete_id']) : [0];

    if (count($delete_id) != 2) {

        $new_tag = $_POST['new_tag'];
        $old_tag = $_POST['old_tag'];

        $old_tag_id = get_tag_id($old_tag);
        
        if ($old_tag_id === null) {
            die("Old tag does not exist");
        }
        if (get_tag_id($new_tag) === null) {
            $new_tag_id = create_tag($new_tag, get_tag_count($old_tag_id));
            var_dump(get_tag_count($old_tag_id));
        } else {
            $new_tag_id = get_tag_id($new_tag);
        }

        $stmt = $mysqli->prepare("INSERT INTO tag_aliases (new_tag, old_tag) VALUES (?, ?)");
        $stmt->bind_param("ss", $new_tag_id, $old_tag_id);
        $stmt->execute();
        $stmt->close();

        header("Location: recount-tags.php");
        exit();

    } else {
        $stmt = $mysqli->prepare("DELETE FROM tag_aliases WHERE new_tag = ? AND old_tag = ?");
        $stmt->bind_param("ss", $delete_id[0], $delete_id[1]);
        $stmt->execute();
        $stmt->close();

        header("Location: recount-tags.php");
        exit();
    }
    header("Location: aliases.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Tag alias</title>
</head>
<body>
    <h1>Add Tag Alias</h1>
    <ul>
        <li><a href="/index.php">Home</a></li>
        <li><a href="/ext/admin/main.php">Admin Console</a></li>
    </ul>

    <form method="post" action="">

        <label for="old_tag">Old Tag:</label>
        <input type="text" id="old_tag" name="old_tag" required>

        <label for="new_tag">New Tag:</label>
        <input type="text" id="new_tag" name="new_tag" required>
        <button type="submit">Add Tag</button>

    </form>
    <?php
    if ($result && $result->num_rows > 0) {

        echo "<table>";
        echo "<tr><th>New Tag</th><th>Old Tag</th><th>Action</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>" . htmlspecialchars($row['new_tag_name']) . "</td><td>" . htmlspecialchars($row['old_tag_name']) . "</td>";
            echo "<td><form method='post' action='aliases.php'><input type='hidden' name='delete_id' value='" . $row['new_tag'] . "_" . $row['old_tag'] . "'><button type='submit'>Delete</button></form></td></tr>";
        }
        echo "</table>";
    } else {
        echo "No tag aliases found.";
    }
    ?>
</body>
</html>