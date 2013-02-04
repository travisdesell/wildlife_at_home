<?php
// This file is part of BOINC.
// http://boinc.berkeley.edu
// Copyright (C) 2008 University of California
//
// BOINC is free software; you can redistribute it and/or modify it
// under the terms of the GNU Lesser General Public License
// as published by the Free Software Foundation,
// either version 3 of the License, or (at your option) any later version.
//
// BOINC is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// See the GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with BOINC.  If not, see <http://www.gnu.org/licenses/>.

echo "
<head>
<link rel='stylesheet' href='jqw/jqwidgets/styles/jqx.base.css' type='text/css'>
<link rel='stylesheet' href='jqw/jqwidgets/styles/jqx.classic.css' type='text/css'>

<script type='text/javascript' src='jqw/scripts/jquery-1.8.0.min.js'></script>

<script type='text/javascript' src='jqw/jqwidgets/jqxcore.js'></script>
<script type='text/javascript' src='jqw/jqwidgets/jqxbuttons.js'></script>

<script type='text/javascript' src='flowplayer/flowplayer-3.2.8.min.js'></script>
<script type='text/javascript' src='flowplayer/flowplayer.playlist-3.2.8.min.js'></script>

<script type='text/javascript' src='test_ff.js'></script>
</head>

<html>

<video width=700 id='wildlife_video' controls='controls' src='http://wildlife.und.edu/video/wildlife/streaming_2/oil_development/sharptailed_grouse/Lostwood/150.622_hatch/5-29-2012_150622/CH00_20120529_203231MN_CHILD0.mp4'>
    This video requires a browser that supports HTML5 video.
</video>

<table>
<tr>
<td>
<div id='jqxWidget'>
    <input type='button' id='fast_backward_button' value='fast backward'></div>
</div>
</td>

<td>
Speed:<input type='text' id='speed_textbox' value='1' readonly='readonly'>
</td>

<td>
<div id='jqxWidget'>
    <input type='button' id='fast_forward_button' value='fast forward'></div>
</div>
</td>
</tr>
<table>
";

?>
