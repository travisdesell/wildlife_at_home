<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/footer.php");
require_once($cwd[__FILE__] . '/../../citizen_science_grid/user.php');

$user = csg_get_user(false);
$user_id = $user['id'];

print_header("Wildlife@Home: IDX Verifier",  "", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

echo "
<div class='container'>
<h2>IDX Validator</h2>";

if (isset($_FILES['userfile'])) {
    require_once($cwd[__FILE__] . "/../../citizen_science_grid/tools/idx.php");

    $count = count($_FILES['userfile']['tmp_name']);

    if ($count > 1) {
        echo "<div class='panel-group'>";
    }

    for ($i = 0; $i < $count; ++$i) {
        $filename = $_FILES['userfile']['tmp_name'][$i];
        $uploadname = basename($_FILES['userfile']['name'][$i]);
        if (!is_uploaded_file($filename)) {
            echo "
<div class='panel panel-warning'>
    <div class='panel-heading'>Upload Error</div>
    <div class='panel-body'>File not uploaded to server.</div>";
        } else {
            $meta = array();
            try {
                $idx = IDX::fromFile($filename, $meta);
                echo "
<div class='panel panel-success'>
    <div class='panel-heading'>Success!</div>
    <div class='panel-body'>
        <p>The IDX file appears to be correctly formatted.</p>";
            } catch (Exception $e) {
                echo "
<div class='panel panel-danger'>
    <div class='panel-heading'>Format Error!</div>
    <div class='panel-body'>
        <p>" . $e->getMessage() . "</p>";
            }

            echo "
<table class='table table-striped'>
    <thead>
        <tr>
        <th>Name</th>
        <th>Value</th>
        </tr>
    </thead>

    <tbody>";

            foreach ($meta as $key => $val) {
                echo "
<tr>
    <td>$key</td>
    <td>$val</td>
</tr>";
            }

            echo "
    </tbody>
</table>
</div>";
        }

        echo "
<div class='panel-footer'><strong>$uploadname</strong></div>
</div>";

        unlink($filename);
    }

    if ($count > 1) {
        echo "</div>";
    }

    unset($_FILES);
} else {
    $maxfilesize = ini_get("upload_max_filesize");
    $maxfilecount = ini_get("max_file_uploads");

    echo "
<div class='panel panel-info'>
    <div class='panel-heading'>Upload an IDX File</div>
    <div class='panel-body'>
        <form enctype='multipart/form-data' method='POST' action=''>
            <div class='form-group'>
                <input type='hidden' name='MAX_FILE_SIZE' value='30000000'>
                <label for='userfile'>IDX file to test</label>
                <input name='userfile[]' type='file' id='userfile' accept='.idx' multiple='multiple'>
                <h6>Max file size: $maxfilesize</h6>
                <h6>Max file count: $maxfilecount</h6>
            </div>
            <button type='submit' class='btn btn-primary'>Upload Files</button>
        </form>
    </div>
</div>";
}

echo "</div>";

print_footer('','');

?>
