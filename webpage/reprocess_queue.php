<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

if (count($argv) != 3) {
    die("Error, invalid arguments. usage: php $argv[0] <project_id> <species_id>\n");
}

$project_id = $argv[1];
$species_id = $argv[2];

$command = "php ${cwd[__FILE__]}/reprocess_queue_background.php $project_id $species_id";

require_once($cwd[__FILE__] . "/data_interface/BackgroundProcess.php");
$process = new Cocur\BackgroundProcess\BackgroundProcess($command);
$process->run();

?>
