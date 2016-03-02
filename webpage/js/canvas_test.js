var species = [];
var options = "";

var newRectCallback = function(id) {
    table = "<div class='panel panel-primary' id='S" + id + "'>" +
        "<div class='panel-heading'>" +
            "Selection " + id +
			"<button type='button' class='close' data-dismiss='modal' aria-hidden='true' id='remove" + id + "'>X</button>" +
        "</div>" +
        "<div class='panel-body'>" +
            "<select class='form-control' id='species" + id + "'>" + options + "</select>" +
            "<div class='input-group'>" +
                "<input type='text' class='form-control disabled' value='On Nest?' disabled>" +
                "<span class='input-group-addon'>" +
                    "<input type='checkbox'>" +
                "</span>" +
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

    $("#selection-information").append(table);
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
    "logging": true,
    "callback": newRectCallback,
    "deleteCallback": deleteCallback
});

img.onload = function() {
    cs.resizeFunc(cs);
};

$(document).ready(function() {
    $.ajax({
        url: "http://csgrid.org/csg/wildlife_mmattingly/canvas_select.php",
        async: false,
        dataType: 'json',
        success: function(data) {
            species = data;
            species.forEach(function(e) {
                options += "<option value='" + e.id + "'>" + e.name + "</option>";
            });
        }
    });
});

$("#skip-button").click(function() {
    $("#submitSuccess").val(0);
    $("#submitForm").submit();
});

$("#nothing-here-button").click(function() {
    // TODO: SAVE
    $("#submitForm").submit();
});

$("#submit-selections-button").click(function() {
    // TODO: SAVE
    $("#submitForm").submit();
});

$(window).resize(function() {
    cs.resizeFunc(cs);
});
