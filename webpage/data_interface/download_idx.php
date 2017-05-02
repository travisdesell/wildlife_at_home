<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

$json = array(
    'pid' => null,
    'status' => null,
    'files' => array(),
    'error' => null,
    'command' => ''
);

function error($message)
{
    global $json;

    $json['status'] = 'error';
    $json['error'] = $message;

    echo json_encode($json);
    return 1;
}

require_once($cwd[__FILE__] . "/parse.php");

// parse our params
$params = new Params();
$params->add_param('expert', Params::T_INTEGER);
$params->add_param('citizen', Params::T_INTEGER);
$params->add_param('start_date', Params::T_DATE);
$params->add_param('end_date', Params::T_DATE);
$params->add_param('bg_ratio', Params::T_INTEGER);
$params->add_param('size', Params::T_INTEGER);

try {
    $params->parse();
} catch (Exception $e) {
    exit(error($e->getMessage()));
}

$expert = is_null($params['expert']) ? false : true;
$citizen = (is_null($params['citizen']) || $params['citizen'] < 0) ? false : true;
if (!$expert && !$citizen) {
    exit(error('No expert or citizen defined'));
}

// matched or unmatched?
$unmatched = $citizen && $params['citizen'] == 0;
$matched = $citizen && !$unmatched;

if ($matched) {
    //exit(error('Matched citizens not yet supported.'));
}

$start_date = is_null($params['start_date']) ? 0 : $params['start_date'];
$end_date = (is_null($params['end_date']) || !$params['end_date']) ? time() : $params['end_date'];
if ($start_date > $end_date) {
    exit(error('End date cannot be before start date.'));
}

$bg_ratio = is_null($params['bg_ratio']) ? 80 : $params['bg_ratio'];
if ($bg_ratio < 0 || $bg_ratio > 99) {
    exit(error('Background ratio must be between 0 and 99.'));
}

$size = is_null($params['size']) ? 18 : $params['size'];
if ($size < 10 || $size > 30) {
    exit(error('Size must be between 10 and 30.'));
}

// cleanup all idx files over 30 minutes old
foreach (glob("/tmp/*.idx") as $idxfile) {
    if (is_file($idxfile) && (time() - filemtime($idxfile)) >= 1800) {
        try {
            unlink($idxfile);
        } catch (Exception $e) {
            // just eat it
        }
    }
}

$script = $cwd[__FILE__] . "/generate_idx.php";
$args = array();

if ($expert)        $args[] = '--expert';
if ($matched)       $args[] = '--matched';
if ($unmatched)     $args[] = '--unmatched';
if ($start_date)    $args[] = "--start_date $start_date";

$args[] = "--end_date $end_date";
$args[] = "--bg_ratio $bg_ratio";
$args[] = "--size $size";

// consistent filename
$filename = "observations";
if ($expert)
    $filename .= '_expert';
if ($unmatched)
    $filename .= '_unmatched';
if ($matched)
    $filename .= '_matched';
if ($start_date)
    $filename .= '_start' . date('Ymd', $start_date);

$filename .= '_end' . date('Ymd', $end_date) . '_' . $bg_ratio . 'percent';
$json['files'][] = "$filename.idx";
$json['files'][] = "${filename}_species.idx";

$command = "php $script " . join(" ", $args);
$json['command'] = $command;

try {
    require_once($cwd[__FILE__] . "/BackgroundProcess.php");
    $process = new Cocur\BackgroundProcess\BackgroundProcess($command);
    $process->run();
    $json['pid'] = $process->getPid();
} catch (Exception $e) {
    exit(error($e->getMessage()));
}

// return
$json['status'] = 'running';
echo json_encode($json);

?>
