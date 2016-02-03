var newRectCallback = function(id) {
    table = "<div class='well well-small' id='S'" + id + "'>" +
        "<table>" +
            "<tr>" +
                "<td align='center'>Selection " + id + "</td>" +
                "<td align='right'><button id='remove" + id + "' class='btn btn-danger delete'>Remove</td>" +
            "</tr>" +
        "</table>";

    $("#selection-information").append(table);
    $("#submit-selections-button").prop("disabled", false);
};

var deleteCallback = function(id, empty) {
    var selectionId = 'S' + id;
};

var cs = new canvasSelector($("#canvas"), $("#canvasImg"), {
    "logging": true,
    "callback": newRectCallback
});

$(document).ready(function() {
    cs.resizeFunc(cs);
    $(document).on("click", ".delete", function() {
        var btnId = $(this).attr("id");
        var id = btnId.substring(6);
    });
});
