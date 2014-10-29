<?php

function generate_count_nav($max_items, $video_min, $video_count, $display_nav_numbers) {
    global $user_id;

    echo "
        <div class='well' style='padding-top: 10px; padding-bottom: 5px; margin-top: 3px; margin-bottom: 15px'> 
            <div class='row'>";

//                    <div class='col-sm-12'>";


    if ($display_nav_numbers) {

        echo "<div class='col-sm-8' style='margin-top:0px; margin-bottom:0px;'>";
        echo "<button class='pull-left btn btn-small btn-default' id='hide-show-sidebar-button' style='margin-right:5px; padding:6px;'>Hide sidebar</button>";
        echo "<ul class='pagination' style='margin-top:0px; margin-bottom:0px;'>";

        if ($video_min > 0) {
            $new_min = $video_min - $video_count;
            if ($new_min < 0) $new_min = 0;

            echo "<li><a class='video-nav-list' id = 'video-list-$new_min' href='javascript:;'>Prev</a> </li>";
        }

        $count = 0;

        $current = $video_min - (2 * $video_count);
        if ($current < 0) $current = 0;

        if ($current > 0) {
            echo "<li> <a class='video-nav-list' id = 'video-list-0' href='javascript:;'>0..$video_count</a> </li>";
        }

        while ($current < $max_items && $count < 5) {
            $next = ($current + $video_count);
            if ($next > $max_items) $next = $max_items;
            $next--;

            if ($current == $video_min) {
                if ($current == $next) {
                    echo "<li><a class='video-nav-list' id='video-list-$current' href='javascript:;'><b>$current</b></a> </li>";
                } else {
                    echo "<li><a class='video-nav-list' id='video-list-$current' href='javascript:;'><b>$current..$next</b></a> </li>";
                }
            } else {
                if ($current == $next) {
                    echo "<li><a class='video-nav-list' id='video-list-$current' href='javascript:;'>$current</a> </li>";
                } else {
                    echo "<li><a class='video-nav-list' id='video-list-$current' href='javascript:;'>$current..$next</a> </li>";
                }
            }

            $current += $video_count;

            $count++;
        }

        if ($current < $max_items) {
            echo "<li> <a class='video-nav-list' id = 'video-list-" . ($max_items - $video_count) . "' href='javascript:;'>" .($max_items - $video_count) . ".." . ($max_items - 1) . "</a> </li>";
        }

        if ($video_min + $video_count < $max_items) {
            $new_min = $video_min + $video_count;

            echo "<li> <a class='video-nav-list' id='video-list-$new_min' href='javascript:;'>Next</a> </li>";
        }

        echo "</ul></div>";
    } else {
        echo "<div class='col-sm-8'></div>";
    }

    echo "
                        <div class='col-sm-4'>

                            <div class='btn-group pull-right'>
                                <button type='button' class='btn btn-small btn-default dropdown-toggle' data-toggle='dropdown' id='sort-by-dropdown' style='padding:6px;'>
                                Sort <span class='caret'></span>
                                </button>
                                <ul class='dropdown-menu'>
                                    <li><a href='javascript:;' class='sort-by-dropdown' sort_value='filename' id='sort-by-filename'>Video Name</a></li>
                                    <li><a href='javascript:;' class='sort-by-dropdown' sort_value='(SELECT id FROM observations WHERE observations.video_segment_id = vs2.id AND observations.user_id = $user_id) DESC' id='sort-by-observation'>Recently Viewed</a></li>
                                </ul>
                            </div>

                            <div class='btn-group pull-right'>
                                <button type='button' class='btn btn-small btn-default dropdown-toggle' data-toggle='dropdown' id='display-videos-button' style='padding:6px;'>
                                Show $video_count videos <span class='caret'></span>
                                </button>
                                <ul class='dropdown-menu'>
                                    <li><a href='javascript:;' class='display-dropdown' count='5' >Display  5 videos</a></li>
                                    <li><a href='javascript:;' class='display-dropdown' count='10'>Display 10 videos</a></li>
                                    <li><a href='javascript:;' class='display-dropdown' count='15'>Display 15 videos</a></li>
                                    <li><a href='javascript:;' class='display-dropdown' count='20'>Display 20 videos</a></li>
                                </ul>
                            </div>

                            <input class='pull-right' style='width:40px; height:34px; margin-top:0px; padding-bottom:0px; margin-left:2px; margin-right:2px;' type='text' id='go-to-textbox' value=''>
                            <button class='pull-right btn btn-small btn-default' id='go-to-button' style='padding:6px;'>Go to: </button>
                        </div>

                    </div>
        </div>
    ";

    //error_log("video_min: $video_min, video_count: $video_count, user_id: $user_id, filter: $filter");
    //error_log("completed get_video_count_nav.php");
}


?>
