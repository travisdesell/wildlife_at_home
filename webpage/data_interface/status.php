<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

$json = array(
    'status' => 'running',
    'error' => null
);

function error($message) {
    global $json;

    $json['status'] = 'error';
    $json['error'] = $message;

    echo json_encode($json);
    return 1;
}

require_once($cwd[__FILE__] . "/parse.php");

// grab the pid
$param = new Params();
$param->add_param('pid', Params::T_INTEGER, true);

try {
    $param->parse();
} catch (Exception $e) {
    exit(error($e->getMessage()));
}

$pid = is_null($param['pid']) ? 0 : $param['pid'];
if ($pid <= 0) {
    exit(error($e->getMessage()));
}

try {
    require_once($cwd[__FILE__] . "/BackgroundProcess.php");
    $process = Cocur\BackgroundProcess\BackgroundProcess::createFromPID($pid);
    if (!$process->isRunning()) {
        $json['status'] = 'done';
    }
} catch (Exception $e) {
    exit(error($e->getMessage()));
}

echo json_encode($json);

?>
