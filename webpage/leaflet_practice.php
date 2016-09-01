<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/footer.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

$leaflet_css = "<link rel='stylesheet' type='text/css' href='wildlife_css/leaflet.css";
$leaflet_js ="<script type='text/javascript' src='js/leaflet_practice.js'></script>";

print_header("Wildlife@Home: Teaching & Learning", "$leaflet_css, $leaflet_js,<link rel='stylesheet' href='https://npmcdn.com/leaflet@1.0.0-rc.3/dist/leaflet.css' /> <script src='https://npmcdn.com/leaflet@1.0.0-rc.3/dist/leaflet.js'></script>", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

echo "
<div class='container'>
	<div class='row'>
		<div class='col-sm-12'>
			<div id='mapid'></div>
		</div>
	</div>
</div>
";

print_footer('Travis Desell, Susan Ellis-Felege, Lindsey Wingate and the Wildlife@Home Team', 'Travis Desell, Susan Ellis-Felege');

echo "</body></html>";
?>
