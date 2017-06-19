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

require_once($cwd[__FILE__] . "/form.php");

// citizen dropdown used throughout
$citizen_dropdown = new FormInputDropdown("citizen", "Citizen");
$citizen_dropdown->append("-1", "None", true);
$citizen_dropdown->append("0", "Unmatched");
$citizen_dropdown->append("1", "Matched");

// object size used throughout
$object_size = new FormInputNumber(
    "size", "Object Size",
    10, 100, 18, "px"
);

$forms = array(
    'idx' => new Form(
        "idx", "download_idx.php",
        "IDX Data",
        "Downloads the IDX files with all the objects from the given the specified parameters."
    ),
    'bg' => new Form(
        "bg", "download_bg.php",
        "Background Data",
        "Downloads an IDX file with background data from the given specified parameters."
    )
);

/// IDX
$forms['idx']->append(new FormInputNumber(
    "bg_ratio", "BG Ratio",
    0, 99, 80, "%"
));

$forms['idx']->append($object_size);

$forms['idx']->append($citizen_dropdown);

$forms['idx']->append(new FormInputCheckbox(
    "expert", "Expert", true
));

/// Background
$forms['bg']->append(new FormInputNumber(
    "bg_ratio", "BG Ratio",
    10, 99, 50, "%"
));

$forms['bg']->append($object_size);

echo "
<div class='container'>

<div class='jumbotron'>
    <h1>Data Downloaders</h1>
    <p>This interface has configurable downloaders for all sorts of data for the Wildlife@Home project.</p>
    <p>None of the data has personally identifiable information.</p>
</div>
";

foreach ($forms as &$form) {
    echo $form->html();
}

echo "
<script src='../js/moment.min.js'></script>
<script src='../js/bootstrap-datetimepicker.min.js'></script>

<script type='text/javascript'>
$(function() {
";

foreach ($forms as &$form) {
    echo $form->js_onload();
}

echo "
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
";

foreach ($forms as &$form) {
    echo $form->js();
}

echo"
</script>
";

print_footer('','');

?>
