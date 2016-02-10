var newRectCallback = function(id) {
    table = "<div class='well well-small' id='S" + id + "'>" +
        "<table>" +
            "<tr>" +
                "<td align='center'>Selection " + id + "</td>" +
                "<td align='right'><button id='remove" + id + "' class='btn btn-danger delete'>Remove</td>" +
            "</tr>" +
        "</table>";

    $("#selection-information").append(table);
    $("#submit-selections-button").prop("disabled", false);
    $("#submit-selections-button").removeClass("disabled");

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
    }
};

var cs = new canvasSelector($("#canvas"), $("#canvasImg"), {
    "logging": true,
    "callback": newRectCallback,
    "deleteCallback": deleteCallback
});

$(document).ready(function() {
});

$("#skip-button").click(function() {
    location.reload();
});
