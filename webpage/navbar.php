<?php

$cwd = __FILE__;
if (is_link($cwd)) $cwd = readlink($cwd);
$cwd = dirname($cwd);

require_once($cwd . '/boinc_db.php');
require_once($cwd . '/wildlife_db.php');
require_once($cwd . '/my_query.php');
require_once($cwd . '/user.php');

function print_navbar($active_items) {
    global $boinc_passwd, $boinc_user, $wildlife_passwd, $wildlife_user;

    $project_scientist = false;
    $user = get_user(false);
    $user_name = "";
    if ($user != null) {
        $user_id = $user['id'];
        $user_name = $user['name'];

        ini_set("mysql.connect_timeout", 300);
        ini_set("default_socket_timeout", 300);

        $boinc_db = mysql_connect("localhost", $boinc_user, $boinc_passwd);
        mysql_select_db("wildlife", $boinc_db);
        if (is_special_user__fixme($user, false)) $project_scientist = true;

    } else {
        $user_name = "Your Account";
    }

    if (!array_key_exists('project_management', $active_items)) $active_items['project_management'] = '';


    echo "
        <!-- NAVBAR
        ================================================== -->
        <div class='navbar navbar-inverse navbar-fixed-top'>
            <div class='navbar-inner'>
                <div class='container-fluid'>
                    <!-- Responsive Navbar Part 1: Button for triggering responsive navbar (not covered in tutorial). Include responsive CSS to utilize. -->
                    <a class='btn btn-navbar' data-toggle='collapse' data-target='.nav-collapse'>
                      <span class='icon-bar'></span>
                      <span class='icon-bar'></span>
                      <span class='icon-bar'></span>
                    </a>

                    <!-- Responsive Navbar Part 2: Place all navbar contents you want collapsed withing .navbar-collapse.collapse. -->
                    <div class='nav-collapse collapse'>
                        <ul class='nav'>
                            <li class='" . $active_items['home'] . "'> <a class='brand' href='http://volunteer.cs.und.edu/wildlife/'>Wildlife@Home</a> </li>

                            <li class='dropdown " . $active_items['watch_video'] . "'>
                              <a href='javascript:;' class='dropdown-toggle' data-toggle='dropdown'>Watch Video<b class='caret'></b></a>
                              <ul class='dropdown-menu'>
                                <li><a href='video_selector.php'>Site and Species Descriptions</a></li>
                                <li class='divider'></li>
                                <li class='nav-header'>Sharp-Tailed Grouse</a></li>
                                <li><a href='watch.php?location=1&species=1'>Belden, ND</a></li>
                                <li><a href='watch.php?location=2&species=1'>Blaisdell, ND</a></li>
                                <li><a href='watch.php?location=3&species=1'>Lostwood Wildlife Refuge, ND</a></li>
                                <li class='divider'></li>
                                <li class='nav-header'>Interior Least Tern</a></li>
                                <li><a href='watch.php?location=4&species=2'>Missouri River, ND</a></li>
                                <li class='divider'></li>
                                <li class='nav-header'>Piping Plover</a></li>
                                <li><a href='watch.php?location=4&species=3'>Missouri River, ND</a></li>
                              </ul>
                            </li>

                            <li class='dropdown " . $active_items['about_wildlife'] . "'>
                              <a href='javascript:;' class='dropdown-toggle' data-toggle='dropdown'>About the Wildlife<b class='caret'></b></a>
                              <ul class='dropdown-menu'>
                                <li class='nav-header'>Sharp-Tailed Grouse</a></li>
                                <li><a href='sharptailed_grouse_info.php'>Ecology and Information</a></li>
                                <li><a href='sharptailed_grouse_training.php'>Training Videos</a></li>
                                <li class='divider'></li>
                                <li class='nav-header'>Interior Least Tern</a></li>
                                <li><a href='javascript:;'>Ecology and Information (Coming Soon)</a></li>
                                <li><a href='javascript:;'>Training Videos (Coming Soon)</a></li>
                                <li class='divider'></li>
                                <li class='nav-header'>Piping Plover </a></li>
                                <li><a href='piping_plover_info.php'>Ecology and Information</a></li>
                                <li><a href='javascript:;'>Training Videos (Coming Soon)</a></li>
                              </ul>
                            </li>


                            <li class='" . $active_items['message_boards'] . "'><a href='http://volunteer.cs.und.edu/wildlife/forum_index.php'>Message Boards</a></li>

                            <li class='dropdown " . $active_items['community'] . "'>
                              <a href='javascript:;' class='dropdown-toggle' data-toggle='dropdown'>Project Information<b class='caret'></b></a>
                              <ul class='dropdown-menu'>
                                <li><a href='publications.php'>Publications</a></li>
                                <li><a href='server_status.php'>Server Status</a></li>
                                <li><a href='profile_menu.php'>Profiles</a></li>
                                <li><a href='user_search.php'>User Search</a></li>
                                <li><a href='language_select.php'>Languages</a></li>
                                <li><a href='boinc_instructions.php'>Instructions, Rules &amp; Policies</a></li>
                                <li><a href='badge_list.php'>Badge Descriptions</a></li>
                                <li class='nav-header'>Top Lists</li>
                                <li><a href='top_bossa_users.php'>Top Bird Watchers</a></li>
                                <li><a href='top_users.php'>Top Users</a></li>
                                <li><a href='top_hosts.php'>Top Hosts</a></li>
                                <li><a href='top_bossa_teams.php'>Top Bird Watching Teams</a></li>
                                <li><a href='top_teams.php'>Top Teams</a></li>
                                <li><a href='stats.php'>More Statistics</a></li>
                              </ul>
                            </li>
                        </ul>

                        <ul class='nav pull-right'>";

if ($project_scientist) {
    $wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
    mysql_select_db("wildlife_video", $wildlife_db);

    $query = "SELECT count(*) FROM timed_observations WHERE report_status = 'REPORTED'";

    $result = attempt_query_with_ping($query, $wildlife_db);
    if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

    $row = mysql_fetch_assoc($result);
    $waiting_review = $row['count(*)'];

    if ($waiting_review == 0) {
        $waiting_review = "";
    } else {
        $waiting_review = " (" . $waiting_review . ")";
    }

    echo "                  <li class='dropdown " . $active_items['project_management'] . " '>
                              <a href='javascript:;' class='dropdown-toggle' data-toggle='dropdown'>Project Mangement$waiting_review<b class='caret'></b></a>
                              <ul class='dropdown-menu'>
                                <li><a href='review_videos.php'>Expert Video Classification$waiting_review</a></li>
                              </ul>
                            </li>";

}


echo "                      <li class='dropdown " . $active_items['preferences'] . " '>
                              <a href='javascript:;' class='dropdown-toggle' data-toggle='dropdown'>$user_name<b class='caret'></b></a>
                              <ul class='dropdown-menu'>
                                <li><a href='home.php'>Your Preferences</a></li>
                                <li><a href='review_videos.php'>Review Videos</a></li>
                                <li><a href='team.php'>Teams</a></li>
                                <li><a href='cert1.php'>Certificate</a></li>
                                <li><a href='apps.php'>Applications</a></li>";

if ($user != null) {
    $url_tokens = url_tokens__fixme($user['authenticator']);
    echo "                      <li class='divider'></li>
                                <li><a href='http://volunteer.cs.und.edu/wildlife/logout.php?$url_tokens'>Log Out</a></li>";
}

echo "                          </ul>
                            </li>

                        </ul>
                    </div> <!-- /.nav-collapse-->
                </div>  <!-- container-fluid -->
            </div>  <!-- navbar-inner -->
        </div>  <!-- navbar -->
    ";

}

?>
