<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require $cwd[__FILE__] . '/../../../mustache.php/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

require_once($cwd[__FILE__] . '/../../../citizen_science_grid/my_query.php');
require_once($cwd[__FILE__] . '/../../../citizen_science_grid/user.php');

function get_tag_dropdowns() {
    global $cwd;

    $query = "SELECT id, category, name, possible_tags FROM observation_types WHERE possible_tags IS NOT NULL AND possible_tags != ''";
    $result = query_wildlife_video_db($query);

    $tag_dropdowns = array();
    while ( $row = $result->fetch_assoc() ) {
        $row['event_id'] = $row['id'];

        $row['observation_id'] = -1;
        if ($row['possible_tags'] != null && $row['possible_tags'] != '') {
            $row['has_tags'] = true;

            $tag_id = 0;
            $possible_tags = explode(", ", $row['possible_tags']);
            $tags = array();
            foreach ($possible_tags as $tag) {
                $current_tag['tag_name'] = $tag;
                $current_tag['tag_id'] = $tag_id;

                $tags[] = $current_tag;
                $tag_id++;
            }

            $row['possible_tags'] = $tags;

            $watch_interface_template = file_get_contents($cwd[__FILE__] . "/../templates/tag_row_template.html");
            $mustache_engine = new Mustache_Engine;
            $tag_dropdowns[$row['id']] = $mustache_engine->render($watch_interface_template, $row);

//            echo ( $mustache_engine->render($watch_interface_template, $row) );
        }
    }

    return $tag_dropdowns;
}

echo json_encode( get_tag_dropdowns() );

?>
