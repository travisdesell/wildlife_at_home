<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/footer.php");

print_header("Wildlife@Home: Chart Collection", "", "wildlife");
print_navbar("Wildlife", "Wildlife@Home", "..");

echo "
<div class='container'>
    <div class='row'>
        <div class='col-sm-12'>
            <section id='identification' class='well'>
                <h2>Collection of Charts</h2>
            </section>

            <section id='timeline' class='well'>
                <div class='page-header'>
                    <h2>Timeline</h2>
                    <p>This chart is a timeline of the user events calculated for a specifed video (see parameters section). This provides information at a glance of how user events compared against each other and the expert(s). If an expert has classified a video it will appear in the top position of the timelime.</p>
                </div>

                <form role='form' action='timeline.php'>
                    <div class='form-group col-sm-2'>
                        <input type='number' class='form-control' name='video_id' placeholder='Video Id'>
                    </div>
                    <button type='submit' class='btn btn-default' >Submit</button>
                </form>
            </section>

            <section id='video-time-plane' class='well'>
                <div class='page-header'>
                    <h2>Video Event Time Plane</h2>
                    <p>This scatterplot shows events plotted with their distance from a matching expert event as size. This means large dots indicate a possible incorrect user event. Color indicates event type and can show which event types users have a difficult time classifying.</p>
                </div>

                <form class='form-horizontal' role='form' action='video_time_plane.php'>
                    <div class='col-sm-2'>
                        <input type='number' class='form-control' name='video_id' placeholder='Video Id'>
                    </div>
                    <button type='submit' class='btn btn-default' >Submit</button>
                </form>
            </section>

            <section id='event-weight-length' class='well'>
                <div class='page-header'>
                    <h2>Event Weight vs Event Length</h2>
                    <p>This scatterplot with trendlines shows the events for a given video and shows what percentange of the total user observation an event is worth and how this compares event legth as a portion of video length.</p>
                    <p>Video ID 6511 is a decent example.</p>
                </div>

                <form role='form' action='event_weight_vs_length.php'>
                    <div class='form-group col-sm-2'>
                        <input type='number' class='form-control' name='video_id' placeholder='Video Id'>
                    </div>
                    <div class='form-group col-sm-2'>
                        <input type='number' class='form-control' name='buffer' placeholder='Buffer'>
                    </div>
                    <div class='form-group col-sm-2'>
                        <input type='number' class='form-control' name='scale_factor' placeholder='Scale Factor' step='0.01'>
                    </div>
                    <button type='submit' class='btn btn-default' >Submit</button>
                </form>
            </section>

            <section id='user-correctness' class='well'>
                <div class='page-header'>
                    <h2>User Correctness</h2>
                    <p>This barchart shows how each user was rated with the three different correctness algorithms and how those scores are affected according to the weight of each event. The grey background is a fair weighting (event correctness / total number of events) and the colored foreground is a scaled weight where short events are given a larger portion of the total observational weight.</p>
                </div>

                <form role='form' action='correctness_user.php'>
                    <div class='form-group col-sm-2'>
                        <input type='number' class='form-control' name='video_id' placeholder='Video Id'>
                    </div>
                    <div class='form-group col-sm-2'>
                        <input type='number' class='form-control' name='buffer' placeholder='Buffer'>
                    </div>
                    <div class='form-group col-sm-2'>
                        <input type='number' class='form-control' name='scale_factor' placeholder='Scale Factor' step='0.01'>
                    </div>
                    <button type='submit' class='btn btn-default' >Submit</button>
                </form>
            </section>

            <section id='correctness-type' class='well'>
                <div class='page-header'>
                    <h2>Event Types vs Correctness</h2>
                    <p>This bar chart shows the percentage of user events that have a matching expert observed event. Each bar group represents the event types and the colors represent the algorithm used to determine the event correctness.</p>
                    <p>The buffer parameters sets the leniency of the buffer match algorithm.</p>
                </div>

                <form role='form' action='correctness_type.php'>
                    <div class='form-group col-sm-2'>
                        <input type='number' class='form-control' name='buffer' placeholder='Buffer'>
                    </div>
                    <button type='submit' class='btn btn-default' >Submit</button>
                </form>
            </section>

            <section id='correctness-difficulty' class='well'>
                <div class='page-header'>
                    <h2>Video Difficulty vs Correctness</h2>
                    <p>This candlestick chart shows the distribution of user correctness vs their perceived difficulty of a video. Correctness in this case is determined by the number of events in their observation that matched an expert event divided by the total number of events they observed for that video.
                </div>

                <form role='form' action='correctness_difficulty.php'>
                    <div class='form-group col-sm-2'>
                        <input type='number' class='form-control' name='buffer' placeholder='Buffer'>
                    </div>
                    <button type='submit' class='btn btn-default' >Submit</button>
                </form>
            </section>
        </div>
    </div>
</div>";

print_footer('Travis Desell, Susan Ellis-Felege and the Wildlife@Home Team', 'Travis Desell, Susan Ellis-Felege');

echo "
</body>
</html>
";

?>
