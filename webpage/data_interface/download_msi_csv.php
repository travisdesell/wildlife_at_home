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

// cleanup all csv files over 30 minutes old
foreach (glob("/tmp/*.csv") as $idxfile) {
    if (is_file($idxfile) && (time() - filemtime($idxfile)) >= 1800) {
        try {
            unlink($idxfile);
        } catch (Exception $e) {
            // just eat it
        }
    }
}

$script = $cwd[__FILE__] . "/generate_msi_csv.php";
$args = array();

if ($expert)        $args[] = '--expert';
if ($matched)       $args[] = '--matched';
if ($unmatched)     $args[] = '--unmatched';
if ($start_date)    $args[] = "--start_date $start_date";

$args[] = "--end_date $end_date";

// consistent filename
$filename = "msi_locations";
if ($expert)
    $filename .= '_expert';
if ($unmatched)
    $filename .= '_unmatched';
if ($matched)
    $filename .= '_matched';
if ($start_date)
    $filename .= '_start' . date('Ymd', $start_date);

$filename .= '_end' . date('Ymd', $end_date) . '.csv';
$json['files'][] = "$filename";

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
