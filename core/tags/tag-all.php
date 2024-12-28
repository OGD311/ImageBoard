<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/tags/tag-functions.php';

$mysqli = $_DB; 
if ($_SERVER['REQUEST_METHOD'] === "GET") {

    $sql = sprintf("SELECT id, name, count
    FROM tags
    WHERE count != 0
    LIMIT " . $_TAGS_ALL_LIMIT . "");
    
    $result = $mysqli->query($sql);

    $tags = [];
    while ($tag = $result->fetch_assoc()) {
        $tags[] = $tag; 
    }

}

if ($result) {
    echo '<h4>Tags</h4>';

    echo '<ul>';
    foreach ($tags as $tag) {
        echo '<div class="tag"> <p>';
        $alias = get_alias($tag['id']) ? get_alias($tag['id']) : null;

        if ($alias) {
            if (!in_array($alias['id'], array_column($tags, 'id'))) {
                echo '<li><span><a id="addTag" onclick="add_and_search(\'' . htmlspecialchars($alias['name']) . '\', true)">+</a> 
                    <a id="removeTag" onclick="add_and_search(\'-' . htmlspecialchars($alias['name']) . '\', false)">-</a></span> 
                    ' . str_replace('_', ' ', htmlspecialchars($alias['name'])) . ' (' . htmlspecialchars($alias['count']) . ')';
            }
            
            $tags = array_filter($tags, function($value) use ($alias) {
                return $value['id'] != $alias['id'];
            });
        } else {
            echo '<li><span><a id="addTag" onclick="add_and_search(\'' . htmlspecialchars($tag['name']) . '\', true)">+</a> 
                <a id="removeTag" onclick="add_and_search(\'-' . htmlspecialchars($tag['name']) . '\', false)">-</a></span> 
                ' . str_replace('_', ' ', htmlspecialchars($tag['name'])) . ' (' . htmlspecialchars($tag['count']) . ')';
        }

        echo '</li>';

        echo '</p></div>';
    }

    if ((count($tags)) == 0) {
        echo '<p>No tags to display!</p>'; 
    }

    echo '</ul>';
} else {
    echo "<p>Error: " . htmlspecialchars($mysqli->error) . "</p>";
}

    

?>
