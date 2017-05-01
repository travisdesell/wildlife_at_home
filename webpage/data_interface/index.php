<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../../../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../../../citizen_science_grid/footer.php");
require_once($cwd[__FILE__] . "/../../../citizen_science_grid/my_query.php");
require_once($cwd[__FILE__] . '/../../../citizen_science_grid/user.php');

$user = csg_get_user();
$user_id = $user['id'];

print_header("Wildlife@Home: Data Downloader",  "<link href='../wildlife_css/bootstrap-datetimepicker.min.css' rel='stylesheet'>", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

echo "
<div class='container'>

<div class='jumbotron'>
    <h1>Data Downloaders</h1>
    <p>This interface has configurable downloaders for all sorts of data for the Wildlife@Home project.</p>
    <p>None of the data has personally identifiable information.</p>
</div>

<div class='row'>
    <div class='col-md-4'>
        <h3>IDX Training Data</h3>
        <p>Downloads the IDX files for training given the specified parameters.</p>
    </div>

    <div class='col-md-8'>
        <p class='hidden' id='idx_form_processing'>Processing... This may take a while.</p>

        <form class='form-horizontal' id='idx_form'>
            <div class='form-group'>
                <label for='idx_picker_start' class='col-sm-2 control-label'>Start Date</label>
                <div class='col-sm-10 input-group date' id='idx_picker_start'>
                    <input type='text' class='form-control' name='start_date'>
                    <span class='input-group-addon'>
                        <span class='glyphicon glyphicon-calendar'></span>
                    </span>
                </div>
            </div>

            <div class='form-group'>
                <label for='idx_picker_end' class='col-sm-2 control-label'>End Date</label>
                <div class='col-sm-10 input-group date' id='idx_picker_end'>
                    <input type='text' class='form-control' name='end_date'>
                    <span class='input-group-addon'>
                        <span class='glyphicon glyphicon-calendar'></span>
                    </span>
                </div>
            </div>

            <div class='form-group'>
                <label for='idx_bg_ratio' class='col-sm-2 control-label'>BG Ratio</label>
                <div class='col-sm-10 input-group'>
                    <input type='number' class='form-control' name='bg_ratio' min='0' max='100' value='80'>
                    <span class='input-group-addon'>%</span>
                </div>
            </div>

            <div class='form-group'>
                <label for='idx_size' class='col-sm-2 control-label'>Object Size</label>
                <div class='col-sm-10 input-group'>
                    <input type='number' class='form-control' name='size' min='10' max='100' value='18'>
                    <span class='input-group-addon'>px<sup>2</sup></span>
                </div>
            </div>

            <div class='form-group'>
                <label for='idx_citizen' class='col-sm-2 control-label'>Citizen</label>
                <div class='col-sm-10 input-group'>
                    <select class='form-control' name='citizen' id='idx_citizen'>
                        <option value='-1'>None</option>
                        <option value='0'>Unmatched</option>
                        <option value='1'>Matched</option>
                    </select>
                </div>
            </div>

            <div class='form-group'>
                <label for='idx_expert' class='col-sm-2 control-label'>Expert</label>
                <div class='col-sm-10 input-group'>
                    <input type='checkbox' id='idx_expert' name='expert' checked>
                </div>
            </div>

            <div class='form-group'>
                <div class='col-sm-offset-2 col-sm-10'>
                    <button type='submit' class='btn btn-primary btn-lg' id='id_form_submit'>Generate</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src='../js/moment.min.js'></script>
<script src='../js/bootstrap-datetimepicker.min.js'></script>

<script type='text/javascript'>
$(function() {
    $('#idx_picker_start').datetimepicker({
        format: 'MM/DD/YYYY'
    });
    $('#idx_picker_end').datetimepicker({
        useCurrent: false,
        format: 'MM/DD/YYYY'
    });

    $('#idx_picker_start').on('dp.change', function(e) {
        $('#idx_picker_end').data('DateTimePicker').minDate(e.date);
    });
    $('#idx_picker_end').on('dp.change', function(e) {
        $('#idx_picker_start').data('DateTimePicker').maxDate(e.date);
    });
});

function hide_form(objForm, objProcess) {
    objForm.addClass('hidden');
    objProcess.removeClass('hidden');
}

function show_form(objForm, objProcess) {
    objProcess.addClass('hidden');
    objForm.removeClass('hidden');
}

function error_message(message, objForm, objProcess) {
    show_form(objForm, objProcess);
    alert(message);
}

function check_status(data, objForm, objProcess) {
    $.post('status.php', data)
        .done(function(status) {
            status = $.parseJSON(status);
            if (status.status == 'error') {
                error_message(status.error, objForm, objProcess);
                return;
            } else if (status.status == 'done') {
                $.each(data.files, function(index, value) {
                    window.open('download.php?filename=' + value);
                });

                show_form(objForm, objProcess);
                return;
            }

            // keep going
            check_status(data, objForm, objProcess);
        })
        .fail(function() {
            error_message('Error 500', objForm, objProcess);
        });
}

$('#idx_form').submit(function() {
    var form = $('#idx_form');
    var form_processing = $('#idx_form_processing');

    hide_form(form, form_processing);

    $.post('download_idx.php', form.serialize())
        .done(function(data) {
            data = $.parseJSON(data);

            if (data.status == 'error') {
                error_message(data.error, form, form_processing);
                return;
            }

            console.log(data);

            check_status(data, form, form_processing);
        })
        .fail(function() {
            error_message('Error 500', form, form_processing);
        });

    event.preventDefault();
});

</script>
";

print_footer('','');

?>
