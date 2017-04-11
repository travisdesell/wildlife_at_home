<?php

if (count($argv) != 2) {
    die("Error, invalid arguments. usage: php $argv[0] <target_directory>\n");
}

function create_link(string $cwd, string $target, string $filename)
{
    $command = "ln -s $cwd/$filename $target/$filename";
    echo "\t$command\n";
    shell_exec("rm $target/$filename");
    shell_exec($command);
}

$target = rtrim($argv[1], '/');
$cwd = rtrim(dirname(__FILE__), '/');

echo "cwd:    $cwd\n";
echo "target: $target\n";

// all the filename globs to symlink
// the key is the glob and the value is a blacklist for unwanted files
$globs = array(
    '*.php' => array(
        "boinc_db.php",
        "wildlife_db.php"
    ),
    '*.js' => array(),
    '*.css' => array(),
    '*.docx' => array(),
);

// all of the directories to symlink
$dirs = array(
    'clips_grouse',
    'data_interface',
    'expert_interface',
    'images',
    'js',
    'publications',
    'review_interface',
    'watch_interface',
    'wildlife_badges',
    'wildlife_css'
);

echo "\nLinking globs...\n";
foreach ($globs as $glob => $blacklist) {
    echo "glob: '$glob'\n";
    foreach (glob($glob) as $filename) {
        if (in_array($filename, $blacklist, true)) {
            echo "\tNot copying '$filename' beacuse it is in the blacklist.\n";
            continue;
        }

        create_link($cwd, $target, $filename);
    }
}

echo "\nLinking directories...\n";
foreach ($dirs as $dir) {
    create_link($cwd, $target, $dir);
}

?>
