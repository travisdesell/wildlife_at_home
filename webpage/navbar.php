<?php

function print_navbar($active_items) {

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
                              <a href='#' class='dropdown-toggle' data-toggle='dropdown'>Watch Video<b class='caret'></b></a>
                              <ul class='dropdown-menu'>
                                <li><a href='http://volunteer.cs.und.edu/wildlife/video_selector.php'>Site and Species Descriptions</a></li>
                                <li class='divider'></li>
                                <li class='nav-header'>Sharp-Tailed Grouse</a></li>
                                <li><a href='watch.php?site=1&species=1'>Belden, ND</a></li>
                                <li><a href='watch.php?site=2&species=1'>Blaisdell, ND</a></li>
                                <li><a href='watch.php?site=3&species=1'>Lostwood Wildlife Refuge, ND</a></li>
                                <li class='divider'></li>
                                <li class='nav-header'>Interior Least Tern</a></li>
                                <li><a href='watch.php?site=4&species=2'>Missouri River, ND</a></li>
                                <li class='divider'></li>
                                <li class='nav-header'>Piping Plover</a></li>
                                <li><a href='watch.php?site=4&species=3'>Missouri River, ND</a></li>
                              </ul>
                            </li>

                            <li class='dropdown " . $active_items['about_wildlife'] . "'>
                              <a href='#' class='dropdown-toggle' data-toggle='dropdown'>About the Wildlife<b class='caret'></b></a>
                              <ul class='dropdown-menu'>
                                <li class='nav-header'>Sharp-Tailed Grouse</a></li>
                                <li><a href='sharptailed_grouse_info.php'>Ecology and Information</a></li>
                                <li><a href='sharptailed_grouse_training.php'>Training Videos</a></li>
                                <li class='divider'></li>
                                <li class='nav-header'>Interior Least Tern</a></li>
                                <li><a href='#'>Ecology and Information (Coming Soon)</a></li>
                                <li><a href='#'>Training Videos (Coming Soon)</a></li>
                                <li class='divider'></li>
                                <li class='nav-header'>Piping Plover </a></li>
                                <li><a href='#'>Ecology and Information (Coming Soon)</a></li>
                                <li><a href='#'>Training Videos (Coming Soon)</a></li>
                              </ul>
                            </li>


                            <li class='" . $active_items['message_boards'] . "'><a href='http://volunteer.cs.und.edu/wildlife/forum_index.php'>Message Boards</a></li>

                            <li class='dropdown " . $active_items['preferences'] . "'>
                              <a href='#' class='dropdown-toggle' data-toggle='dropdown'>Your Account<b class='caret'></b></a>
                              <ul class='dropdown-menu'>
                                <li><a href='home.php'>Your Preferences</a></li>
                                <li><a href='team.php'>Teams</a></li>
                                <li><a href='cert1.php'>Certificate</a></li>
                                <li><a href='apps.php'>Applications</a></li>
                              </ul>
                            </li>
                            
                            <li class='dropdown " . $active_items['community'] . "'>
                              <a href='#' class='dropdown-toggle' data-toggle='dropdown'>Community<b class='caret'></b></a>
                              <ul class='dropdown-menu'>
                                <li><a href='profile_menu.php'>Profiles</a></li>
                                <li><a href='user_search.php'>User Search</a></li>
                                <li><a href='language_select.php'>Languages</a></li>
                                <li class='nav-header'>Top Lists</li>
                                <li><a href='top_bossa_users.php'>Top Bird Watchers</a></li>
                                <li><a href='top_users.php'>Top Users</a></li>
                                <li><a href='top_hosts.php'>Top Hosts</a></li>
                                <li><a href='top_teams.php'>Top Teams</a></li>
                                <li><a href='stats.php'>More Statistics</a></li>
                              </ul>
                            </li>
                        </ul>
                    </div> <!-- /.nav-collapse-->
                </div>  <!-- container-fluid -->
            </div>  <!-- navbar-inner -->
        </div>  <!-- navbar -->
    ";

}

?>
