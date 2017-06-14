<?php

/* download a csv for the msi true counts */

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../../citizen_science_grid/my_query.php");

if (count($argv) < 5) {
    die("Usage php " . $argv[0] . " outfilebase bg.idx data.idx species.idx\n");
}

$outfilebase    = $argv[1];
$bgfile         = $argv[2];
$datafile       = $argv[3];
$speciesfile    = $argv[4];

require_once($cwd[__FILE__] . "/../../../citizen_science_grid/tools/idx.php");

// load in the data
try {
    $datameta = array();
    $speciesmeta = array();

    $dataidx = IDX::fromFile($datafile, $datameta);
    $speciesidx = IDX::fromFile($speciesfile, $speciesmeta);

    echo "Data: ";
    print_r($datameta);

    echo "\nSpecies: ";
    print_r($speciesmeta);

    // make sure the data and species are the same length
    if ($datameta['Elements'] != $speciesmeta['Elements']) {
        die('Data and species must be the same length.');
    }

    $bgmeta = array();
    $bgidx  = IDX::fromFile($bgfile, $bgmeta);

    echo "\nBackground: ";
    print_r($bgmeta);
} catch (Exception $e) {
    die($e->getMessage());
}

echo "\nCombining data...\n";
foreach ($bgidx as $bg) {
    $dataidx[] = $bg;
    $speciesidx[] = array(-1);
}

echo "\nSaving the files...\n";

// save the idx files
$dataidx->saveToFile("${outfilebase}.idx");
$speciesidx->saveToFile("${outfilebase}_species.idx");

exit(0);

?>
