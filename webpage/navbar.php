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
                            <li class='brand'>Wildlife@Home </li>
                            <li class='" . $active_items['home'] . "'><a href='http://volunteer.cs.und.edu/wildlife/index.php'>Home</a></li>
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

                            <li class='dropdown " . $active_items['about_wildlife'] . "'>
                              <a href='#' class='dropdown-toggle' data-toggle='dropdown'>About the Wildlife<b class='caret'></b></a>
                              <ul class='dropdown-menu'>
                                <li><a href='sharptailed_grouse_info.php'>Sharptailed Grouse</a></li>
                                <li><a href='#'>Piping Plover (Coming Soon)</a></li>
                                <li><a href='#'>Least Tern (Coming Soon)</a></li>
                              </ul>
                            </li>

                            
                            <li class='dropdown " . $active_items['community'] . "'>
                              <a href='#' class='dropdown-toggle' data-toggle='dropdown'>Community<b class='caret'></b></a>
                              <ul class='dropdown-menu'>
                                <li><a href='profile_menu.php'>Profiles</a></li>
                                <li><a href='user_search.php'>User Search</a></li>
                                <li><a href='language_select.php'>Languages</a></li>
                                <li class='nav-header'>Top Lists</li>
                                <li><a href='top_bossa_users.php'>Top Video Watchers</li>
                                <li><a href='top_users.php'>Top Users</li>
                                <li><a href='top_hosts.php'>Top Hosts</li>
                                <li><a href='top_teams.php'>Top Teams</li>
                                <li><a href='stats.php'>More Statistics</a></li>
                              </ul>
                            </li>

                            <li><a href='http://volunteer.cs.und.edu/wildlife/index.php#contact'>Contact</a></li>
                        </ul>
                    </div> <!-- /.nav-collapse-->
                </div>  <!-- container-fluid -->
            </div>  <!-- navbar-inner -->
        </div>  <!-- navbar -->
    ";

}

?>
