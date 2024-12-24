<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/tags/tag-functions.php';

$mysqli = $_DB;

$searchArray = (explode(',', $_POST['search']));

if (!empty($_POST["word"])) {
    $searchTerm = $_POST["word"] . '%';
    $sql = "SELECT id, name, count FROM tags WHERE name LIKE '" . htmlspecialchars($searchTerm) . "' AND count != 0";
 
    if (!empty($searchArray)) { 
        foreach (array_filter($searchArray) as $key => $term) {
            $sql .= " AND name NOT LIKE '$term'";
        }
    }

    $sql .= " ORDER BY count DESC LIMIT " . $_TAGS_ALL_LIMIT;
 

    $stmt = $mysqli->prepare($sql);
    if ($stmt) {

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) { ?>
                <?php while ($tag = $result->fetch_assoc()) { ?>
                    <li onclick="remove_from_search('<?php echo htmlspecialchars(($_POST["word"])); ?>'); add_to_search('<?php echo htmlspecialchars($tag['name']); ?>', true);">
                        <a class="dropdown-item">
                            <?php echo str_replace('_', ' ', htmlspecialchars($tag["name"]));
                            if (get_alias($tag['id']) != null) { 
                                $alias = get_alias($tag['id']);
                                echo ' -> ' . str_replace('_', ' ', htmlspecialchars($alias["name"]));
                            } ?>
                        </a>
                    </li>
                <?php } ?>
        <?php } else { ?>
            <li>
                <a class="dropdown-item">No tags found</a>
            </li>
        <?php }
 

        $stmt->close();
    } else {
        echo '
        <li>
            <a class="dropdown-item">Error preparing statement: ' . $mysqli->error . '</a>
        </li>
        ';
    }
}
?>
