<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/parse.php");

// grab the filename and encoding type
$param = new Params();
$param->add_param('filename', Params::T_STRING, true);
$param->add_param('encoding', Params::T_STRING);

try {
    $param->parse();
} catch (Exception $e) {
    exit($e->getMessage());
}

$filename = is_null($param['filename']) ? '' : $param['filename'];
$encoding = is_null($param['encoding']) ? 'application/octet-stream' : $param['encoding'];

$archive_name = "/tmp/$filename";
$basename = basename($archive_name);
$size = 0;

// make sure we have the file
if (!is_file($archive_name) || ($size = filesize($archive_name)) <= 0) {
    exit("Unable to find file: $filename");
}

// download the file
header("Content-type: $encoding");
header("Content-Disposition: attachment; filename=$basename");
header("Content-Length: $size");
header("Pragma: no-cache");
header("Expires: 0");
readfile($archive_name);

?>
