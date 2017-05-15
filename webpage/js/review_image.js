var species = [];
var options = "";
var lastId = 0;

function resizeSelectionArea() {
    var sic = $("#selection-info-container");
    var margin = parseInt(sic.css('margin-top')) + parseInt(sic.css('margin-bottom')) + parseInt($("#progress_horizontal").css('margin-bottom'));
    var height = $("#col-canvas").height() - $("#row-image-info").height() - $("#row-button-area").height() - margin;
    height = '' + height + 'px';
    sic.height(height);
    sic.css('max-height', height);
}

var newRectCallback = function(id) {
    nest = "";
    nestid = "nest" + id;
    if (nest_confidence) {
        nest = "<label for='" + nestid + "' class='col-sm-2 control-label'>On Nest?</label>" +
            "<div class='col-sm-10'>" +
                "<select class='form-control' id='" + nestid + "'>" +
                    "<option value='0' selected='selected'>No nest</option>" +
                    "<option value='1'>On nest: Low confidence</option>" +
                    "<option value='2'>On nest: High confidence</option>" +
                "</select>" +
            "</div>";
    } else {
        nest = "<div class='col-sm-offset-2 col-sm-10'>" +
                "<div class='checkbox'>" +
                    "<label>" +
                        "<input type='checkbox' id='" + nestid + "'> On nest?" +
                    "</label>" +
                "</div>" +
            "</div>"; 
    }

    if (lastId) {
        $("#S" + lastId).addClass('panel-info');
        $("#S" + lastId).removeClass('panel-success');
    }

    lastId = id;

    table = "<div class='panel panel-primary' id='S" + id + "'>" +
        "<div class='panel-heading'>" +
            "Selection " + id +
			"<button type='button' class='close' data-dismiss='modal' aria-hidden='true' id='remove" + id + "'>X</button>" +
        "</div>" +
        "<div class='panel-body'>" +
            "<div class='form-horizontal'>" +
                "<div class='form-group'>" +
                    "<label for='species" + id + "' class='col-sm-2 control-label'>Species</label>" +
                    "<div class='col-sm-10'>" +
                        "<select class='form-control' id='species" + id + "'>" + options + "</select>" +
                    "</div>" +
                "</div>" +
                "<div class='form-group'>" +
                    nest +
                "</div>" +
            "</div>" +
            "<div class='input-group hidden' id='otherdiv" + id + "'>" +
                "<div class='input-group'>" +
                    "<span class='input-group-addon'>" +
                        "Species:" +
                    "</span>" +
                    "<input id='other" + id + "' type='text' class='form-control' placeholder='Species name'>" +
                "</div>" +
            "</div>" +
        "</div>" +
    "</div>";

    $("#selection-information").prepend(table);
    resizeSelectionArea();

    $("#submit-selections-button").prop("disabled", false);
    $("#submit-selections-button").removeClass("disabled");
    $("#nothing-here-button").prop("disabled", true);
    $("#nothing-here-button").addClass("disabled");

    $("#species" + id).change(function (e) {
        var selected = $("#species" + id + " option:selected");
        if (selected.text() == "Other") {
            $("#other" + id).val("");
            $("#otherdiv" + id).removeClass('hidden');
        } else {
            $("#otherdiv" + id).addClass('hidden');
        }
    });

    $("#remove" + id).click(function (e) {
        cs.removeRect(cs, e.target.id.substring(6));
    });
};

var deleteCallback = function(id, empty) {
    var selectionId = 'S' + id;
    $('#'+selectionId).remove();
    resizeSelectionArea();

    if (lastId == id) {
        lastId = 0;
    }

    if (empty) {
        $("#submit-selections-button").prop("disabled", true);
        $("#submit-selections-button").addClass("disabled");
        $("#nothing-here-button").prop("disabled", false);
        $("#nothing-here-button").removeClass("disabled");
    }
};

var img = new Image();
img.src = imgsrc;
var cs = new canvasSelector($("#canvas"), img, {
    "logging": false,
    "callback": newRectCallback,
    "deleteCallback": deleteCallback,
    "progressBarX": "progress_horizontal",
    "progressBarY": "progress_vertical",
    "scaleArea": "scale_span",
    "boxArea": "selection-information"
});

img.onload = function() {
    cs.resizeFunc(cs);
    resizeSelectionArea();
};

$(document).ready(function() {
    $.ajax({
        url: "https://csgrid.org/csg/wildlife/canvas_select.php",
        async: false,
        dataType: 'json',
        data: 'p=' + project_id,
        type: 'POST',
        success: function(data) {
            species = data;
            var keys = Object.keys(species);
            keys.sort();

            for (var i = 0; i < keys.length; i++) {
                var key = keys[i];
                var name = key;
                // hardcoded temporarily, update to DB
                if (name == 'Lesser Snow Goose')
                    name = 'Lesser Snow Goose, White Phase';

                options += "<option value='" + species[key] + "'";
                if (species_id == species[key])
                    options += " selected='selected'";
                options += ">" + name + "</option>";
            }
        }
    });

    $('#submitForm').submit(function(e) {
        // add the info for each rectangle
        var boxes = [];
        cs.rectangles.forEach(function(e) {
            var species = $("#species" + e.id + " option:selected")[0].value;
            var nest = 0;
            if (nest_confidence) {
                nest = $("#nest" + e.id + " option:selected")[0].value;
            } else {
                nest = $("#nest" + e.id)[0].checked ? 1 : 0;
            }

            boxes.push({
                'x': e.left,
                'y': e.top,
                'width': e.width,
                'height': e.height,
                'species_id': species,
                'on_nest': nest
            });
        });

        var formData = {
            'metadata': {
                'image_id' : $('input[name=image_id]').val(),
                'comments' : $('#comment-area').val(),
                'start_time': $('input[name=submitStart]').val(),
                'nothing_here': cs.rectangles.length == 0 ? 1 : 0
            },
            'boxes': boxes
        };

        //console.log(formData);

        // process
        $.ajax({
            type: 'POST',
            url: 'canvas_submission.php',
            data: formData,
            dataType: 'json',
            encode: true
        })
            .done(function(data) {
                window.scrollTo(0,0);
                if (!data['success']) {
                    $("#ajaxalert").removeClass('alert-success');
                    $("#ajaxalert").removeClass('alert-info');
                    $("#ajaxalert").addClass('alert-danger');
                    $("#ajaxalert").html("<strong>Error!</strong> " + data['errors']);
                    $("#ajaxalert").removeClass('hidden');
                } else {
                    $("#ajaxalert").removeClass('alert-danger');
                    $("#ajaxalert").removeClass('alert-info');
                    $("#ajaxalert").addClass('alert-success');

                    var html = '';
                    if (data['count'] == 0) html = 'Nothing here submitted.';
                    else if (data['count'] == 1) html = '1 rectangle added.';
                    else html = data['count'] + ' rectangles added.';

                    $("#ajaxalert").html("<strong>Success!</strong> " + html + ' Reloading page in 1-3 seconds.');
                    $("#ajaxalert").removeClass('hidden');

                    setTimeout(function() {
                        if (can_reload) {
                            location.reload();
                        } else {
                            window.location.href = reload_location;
                        }
                    }, 1000);
                }
            });

            //.fail(function(data) {
            //    console.log(data);
            //});
        
        // stop normal submission
        e.preventDefault();
    });
});

// allow arrow key movement in the frame
var currentPan = {
    'x': 0,
    'y': 0,
    'status': {
        'left': 0,
        'right': 0,
        'up': 0,
        'down': 0 
    }
};

function isPanning() {
    return  currentPan['status']['left'] ||
            currentPan['status']['right'] ||
            currentPan['status']['up'] ||
            currentPan['status']['down'];
}

$(document).keydown(function(e) {
    var scrollAmount = 20;
    var evtype = isPanning() ? 'panmove' : 'panstart';

    switch (e.which) {
        case 37: // left
            currentPan['left'] += 1;
            break;
        case 38: // up
            currentPan['up'] += 1;
            break;
        case 39: // right
            currentPan['right'] += 1;
            break;
        case 40: // down
            currentPan['down'] += 1;
            break;
        default:
            return;
    }

    if (currentPan['left'] && !currentPan['right']) {
        currentPan['x'] += scrollAmount * ((currentPan['left'] + 3) / 4);
    }
    else if (currentPan['right'] && !currentPan['left']) {
        currentPan['x'] -= scrollAmount * ((currentPan['right'] + 3) / 4);
    } else if (currentPan['left'] && currentPan['right']) {
        currentPan['left'] = 1;
        currentPan['right'] = 1;
    }

    if (currentPan['up'] && !currentPan['down']) {
        currentPan['y'] += scrollAmount * ((currentPan['up'] + 3) / 4);
    }
    else if (currentPan['down'] && !currentPan['up']) {
        currentPan['y'] -= scrollAmount * ((currentPan['down'] + 3) / 4);
    } else if (currentPan['up'] && currentPan['down']) {
        currentPan['up'] = 1;
        currentPan['down'] = 1;
    }

    var ev = {
        'deltaX': currentPan['x'],
        'deltaY': currentPan['y'],
        'center': {
            'x': 1000000,
            'y': 1000000
        },
        'type': evtype
    };

    cs.onPan(cs, ev);
    e.preventDefault();
});

$(document).keyup(function(e) {
    switch (e.which) {
        case 37: // left
            currentPan['left'] = 0;
            break;
        case 38: // up
            currentPan['up'] = 0;
            break;
        case 39: // right
            currentPan['right'] = 0;
            break;
        case 40: // down
            currentPan['down'] = 0;
            break;
        default:
            return;
    }

    if (!isPanning()) {
        var ev = {
            'deltaX': currentPan['x'],
            'deltaY': currentPan['y'],
            'center': {
                'x': 1000000,
                'y': 1000000
            },
            'type': 'panend'
        };

        currentPan['x'] = 0;
        currentPan['y'] = 0;

        cs.onPan(cs, ev);
    }

    e.preventDefault();
});

// scroll to top on reload
window.onbeforeunload = function() {
    window.scrollTo(0,0);
}

$("#discuss-button").click(function() {
    $("#forumContent").val('I would like to discuss the following trail cam image:\n\n[img]' + imgsrc + '[/img]');
    $("#forumPost").submit();
});

$("#skip-button").click(function() {
    location.reload();
});

$("#nothing-here-button").click(function() {
    $("#submitForm").submit();
});

$("#submit-selections-button").click(function() {
    $("#submitForm").submit();
});

$(window).resize(function() {
    cs.resizeFunc(cs);
    resizeSelectionArea();
});
