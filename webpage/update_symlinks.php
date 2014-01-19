<?php

if (count($argv) != 2) {
    die("Error, invalid arguments. usage: php $argv[0] <target_directory>\n");
}

$target = $argv[1];
$cwd = dirname(__FILE__);

echo "cwd:    $cwd\n";
echo "target: $target\n";

foreach (glob("*.php") as $filename) {
    $command = "ln -s $cwd/$filename $target/$filename";
    echo "$command\n";
    shell_exec($command);
}

foreach (glob("*.js") as $filename) {
    //echo $filename . "\n";
    $command = "ln -s $cwd/$filename $target/$filename";
    echo "$command\n";
    shell_exec($command);
}

?>
