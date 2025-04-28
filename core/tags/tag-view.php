<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/tags/tag-functions.php';

$mysqli = $_DB; 
if ($_SERVER['REQUEST_METHOD'] === "GET") {

    $sql = sprintf("SELECT t.id, t.name, t.category, t.count
    FROM tags t
    JOIN post_tags pt ON t.id = pt.tag_id
    WHERE pt.post_id = '%s'", htmlspecialchars($post['id']) );
    
    $result = $mysqli->query($sql);

    $tags = [];
    while ($tag = $result->fetch_assoc()) {
        $tag = array_map('strtolower', $tag);
        $tags[] = $tag;
    }

    


}


if ($result) { 
    $previousCategory = null;
    echo '<h4>Tags</h4>';

    foreach ($tags as $tag) {

        $tag_category = strtolower($tag['category']);

        if ($tag_category != $previousCategory) {
            echo '</ul>
            <h3 class=' . htmlspecialchars($tag_category) . '>' . htmlspecialchars(ucfirst($tag_category)) . '</h3>
            <ul id="tags-list">';
        }
        $alias = get_alias($tag['id']) ?? null;

        if ($alias) {
            if (!in_array($alias['id'], array_column($tags, 'id'))) {
                echo '<li class=' . htmlspecialchars($tag_category) .'><span><a id="addTag" onclick="add_and_search(\'' . htmlspecialchars($alias['name']) . '\', true)">+</a> 
                    <a id="removeTag" onclick="add_and_search(\'-' . htmlspecialchars($alias['name']) . '\', false)">-</a></span> 
                    ' . str_replace('_', ' ', htmlspecialchars($alias['name'])) . ' (' . htmlspecialchars($alias['count']) . ')';
            }
            
            $tags = array_filter($tags, function($value) use ($alias) {
                return $value['id'] != $alias['id'];
            });
        } else {
            echo '<li class=' . htmlspecialchars($tag_category) .'><span><a id="addTag" onclick="add_and_search(\'' . htmlspecialchars($tag['name']) . '\', true)">+</a> 
                <a id="removeTag" onclick="add_and_search(\'-' . htmlspecialchars($tag['name']) . '\', false)">-</a></span> 
                ' . str_replace('_', ' ', htmlspecialchars($tag['name'])) . ' (' . htmlspecialchars($tag['count']) . ')';
        }

        echo '</li>';
        $previousCategory = $tag_category;
    }

    if ((count($tags)) == 0) {
        echo '<p>No tags to display!</p>'; 
    }

    echo '</ul>';
} else {
    echo "<p>Error: " . htmlspecialchars($mysqli->error) . "</p>";
}

    

?>
