<?php

function generate_count_nav($max_items, $video_min, $video_count, $display_nav_numbers) {

    echo "
        <div class='well well-large' style='padding-top: 10px; padding-bottom: 5px; margin-top: 3px; margin-bottom: 15px'> 
            <div class='row-fluid'>
                    <div class='span12'>";


    if ($display_nav_numbers) {

        echo "<div class='pagination span8' style='margin-top:0px; margin-bottom:0px;'><ul>";
        if ($video_min > 0) {
            $new_min = $video_min - $video_count;
            if ($new_min < 0) $new_min = 0;

            echo "<li><a class='video-nav-list' id = 'video-list-$new_min' href='#'>Prev</a> </li>";
        }

        $count = 0;

        $current = $video_min - (2 * $video_count);
        if ($current < 0) $current = 0;

        if ($current > 0) {
            echo "<li> <a class='video-nav-list' id = 'video-list-0' href='#'>0..$video_count</a> </li>";
        }

        while ($current < $max_items && $count < 5) {
            $next = ($current + $video_count);
            if ($next > $max_items) $next = $max_items;
            $next--;

            if ($current == $video_min) {
                if ($current == $next) {
                    echo "<li><a class='video-nav-list' id='video-list-$current' href='#'><b>$current</b></a> </li>";
                } else {
                    echo "<li><a class='video-nav-list' id='video-list-$current' href='#'><b>$current..$next</b></a> </li>";
                }
            } else {
                if ($current == $next) {
                    echo "<li><a class='video-nav-list' id='video-list-$current' href='#'>$current</a> </li>";
                } else {
                    echo "<li><a class='video-nav-list' id='video-list-$current' href='#'>$current..$next</a> </li>";
                }
            }

            $current += $video_count;

            $count++;
        }

        if ($current < $max_items) {
            echo "<li> <a class='video-nav-list' id = 'video-list-" . ($max_items - $video_count) . "' href='#'>" .($max_items - $video_count) . ".." . ($max_items - 1) . "</a> </li>";
        }

        if ($video_min + $video_count < $max_items) {
            $new_min = $video_min + $video_count;

            echo "<li> <a class='video-nav-list' id='video-list-$new_min' href='#'>Next</a> </li>";
        }

        echo "</ul></div>";
    } else {
        echo "<div class='span8'></div>";
    }

    echo "
                        <div class='span4'>
                            <div class='btn-group pull-right'>
                                <button type='button' class='btn btn-small btn-default dropdown-toggle' data-toggle='dropdown' id='display-videos-button'>
                                Display $video_count videos <span class='caret'></span>
                                </button>
                                <ul class='dropdown-menu bottom-up'>
                                    <li><a href='#' id='display-5-dropdown'>Display  5 videos</a></li>
                                    <li><a href='#' id='display-10-dropdown'>Display 10 videos</a></li>
                                    <li><a href='#' id='display-20-dropdown'>Display 20 videos</a></li>
                                </ul>
                            </div>

                            <input class='pull-right' style='width:30px; margin-top:0px; padding-bottom:0px; margin-left:2px; margin-right:10px' type='text' id='go-to-textbox' value=''>
                            <button class='pull-right btn btn-small btn-default' id='go-to-button'>Go to: </button>
                        </div>

                    </div>
                </div>
        </div>
    ";

    //error_log("video_min: $video_min, video_count: $video_count, user_id: $user_id, filter: $filter");
    //error_log("completed get_video_count_nav.php");
}


?>
