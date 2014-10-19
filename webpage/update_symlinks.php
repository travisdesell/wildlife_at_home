<?php

if (count($argv) != 2) {
    die("Error, invalid arguments. usage: php $argv[0] <target_directory>\n");
}

$target = $argv[1];
$cwd = dirname(__FILE__);

echo "cwd:    $cwd\n";
echo "target: $target\n";

foreach (glob("*.php") as $filename) {
    if ($filename == "boinc_db.php" || $filename == "wildlife_db.php") {
        echo "Not copying '$filename' because it contains the database passwords and is not needed.\n";
        continue;
    }

    $command = "ln -s $cwd/$filename $target/$filename";
    echo "$command\n";
    shell_exec("rm $target/$filename");
    shell_exec($command);
}

foreach (glob("*.js") as $filename) {
    //echo $filename . "\n";
    $command = "ln -s $cwd/$filename $target/$filename";
    echo "$command\n";
    shell_exec("rm $target/$filename");
    shell_exec($command);
}

foreach (glob("*.css") as $filename) {
    //echo $filename . "\n";
    $command = "ln -s $cwd/$filename $target/$filename";
    echo "$command\n";
    shell_exec("rm $target/$filename");
    shell_exec($command);
}


$command = "ln -s $cwd/wildlife_css $target/wildlife_css";
echo "$command\n";
shell_exec("rm $target/wildlife_css");
shell_exec($command);

$command = "ln -s $cwd/expert_interface $target/expert_interface";
echo "$command\n";
shell_exec("rm $target/expert_interface");
shell_exec($command);

$command = "ln -s $cwd/watch_interface $target/watch_interface";
echo "$command\n";
shell_exec("rm $target/watch_interface");
shell_exec($command);

$command = "ln -s $cwd/publications $target/publications";
echo "$command\n";
shell_exec("rm $target/publications");
shell_exec($command);

$command = "ln -s $cwd/images $target/images";
shell_exec("rm $target/images");
shell_exec($command);

$command = "ln -s $cwd/wildlife_badges $target/wildlife_badges";
shell_exec("rm $target/wildlife_badges");
shell_exec($command);

$command = "ln -s $cwd/review_interface $target/review_interface";
shell_exec("rm $target/review_interface");
shell_exec($command);

$command = "ln -s $cwd/js $target/js";
shell_exec("rm $target/js");
shell_exec($command);
?>
