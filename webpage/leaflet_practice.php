<?php
$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

echo"
<DOCTYPE html>
<html>
<head>
	<title>Leaflet Quick Start Guide Example</title>
	<meta charset='utf-8' />

	<meta name='viewport' content='width=device-width, initial-scale=1.0'>

	<link rel='stylesheet' href='https://npmcdn.com/leaflet@1.0.0-rc.3/dist/leaflet.css' />
</head>
<body>
	<div id='mapid' style='width: 600px; height: 400px'></div>

	<script src=https://npmcdn.com/leaflet@1.0.0-rc.3/dist/leaflet.js'></script>
	<script>
		var mymap = L.map('mapid').setView([51.505, -0.09], 13);
";
?>
