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
                </div>

                <form class='form-horizontal' role='form' action='video_time_plane.php'>
                    <div class='form-group'>
                        <label for='vtp1_id' class='col-sm-2 control-label'>Time Plane 1</label>
                        <div class='col-sm-2'>
                            <input type='number' class='form-control' name='video_id' placeholder='Video Id'>
                        </div>
                        <button type='submit' class='btn btn-default' >Submit</button>
                    </div>
                </form>
                </br>
                <form class='form-horizontal' role='form' action='video_time_plane_2.php'>
                    <div class='form-group'>
                        <label for='vtp1_id' class='col-sm-2 control-label'>Time Plane 2</label>
                        <div class='col-sm-2'>
                            <input type='number' class='form-control' name='video_id' placeholder='Video Id'>
                        </div>
                        <button type='submit' class='btn btn-default' >Submit</button>
                    </div>
                </form>
            </section>

            <section id='correctness-event-length' class='well'>
                <div class='page-header'>
                    <h2>Event Length vs Correctness</h2>
                </div>

                <form role='form' action='correctness_vs_event_length.php'>
                    <div class='form-group col-sm-2'>
                        <input type='number' class='form-control' name='buffer' placeholder='Buffer'>
                    </div>
                    <button type='submit' class='btn btn-default' >Submit</button>
                </form>
            </section>

            <section id='correctness-type' class='well'>
                <div class='page-header'>
                    <h2>Event Types vs Correctness</h2>
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
