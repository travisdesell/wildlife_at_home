<?php

if (count($argv) != 3) {
    die("Error, invalid arguments. usage: php $argv[0] <project_id> <species_id>\n");
}

$project_id = $argv[1];
$species_id = $argv[2];
$cwd = dirname(__FILE__);

print `echo /usr/bin/php -q reprocess_queue_background.php $project_id $species_id | at now`;

?>
