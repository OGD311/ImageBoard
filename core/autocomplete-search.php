<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/tags/tag-functions.php';

$mysqli = $_DB;

$searchArray = (explode(' ', $_POST['search']));
if (!empty($_POST["word"])) {
    $searchTerm = $_POST["word"] . '%';
    $sql = "SELECT id, name, count FROM tags WHERE name LIKE '" . htmlspecialchars($searchTerm) . "'";
 
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

        if ($result->num_rows > 0) { 
            while ($tag = $result->fetch_assoc()) { 
            $alias = get_alias($tag['id']) ? get_alias($tag['id']) : null;
            
            if ($alias) { 
                echo '
                <li onclick="remove_from_search(\'' . htmlspecialchars($_POST["word"]) . '\', this); add_to_search(\'' . htmlspecialchars($alias['name']) . '\', true, this);">
                <a class="dropdown-item">' . str_replace('_', ' ', htmlspecialchars($tag["name"])) . ' -> ' . str_replace('_', ' ', htmlspecialchars($alias["name"])) . '</a>
                </li>';
            } else { 
                echo '
                <li onclick="remove_from_search(\'' . htmlspecialchars($_POST["word"]) . '\', this); add_to_search(\'' . htmlspecialchars($tag['name']) . '\', true, this);">
                <a class="dropdown-item">' . str_replace('_', ' ', htmlspecialchars($tag["name"])) . '</a>
                </li>'; 
            }
            }
        } else { 
            echo '
            <li>
                <a class="dropdown-item">No tags found</a>
            </li>';
        }
 

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
