
$(document).ready(function () {

    var belden_content = "<p>" + (grouse_belden_validated / 3600).toFixed(1) + " hours watched</p>" +
                           "<p>" + (grouse_belden_processed / 3600).toFixed(1) + " hours available</p>" +
                           "<p>" + (grouse_belden_total / 3600).toFixed(1) + " hours total</p>";

    $('#grouse_belden_progress').popover({ placement : 'bottom', html : true,  content : belden_content, title : 'Progress'});


    var blaisdell_content = "<p>" + (grouse_blaisdell_validated / 3600).toFixed(1) + " hours watched</p>" +
                           "<p>" + (grouse_blaisdell_processed / 3600).toFixed(1) + " hours available</p>" +
                           "<p>" + (grouse_blaisdell_total / 3600).toFixed(1) + " hours total</p>";

    $('#grouse_blaisdell_progress').popover({ placement : 'bottom', html : true,  content : blaisdell_content, title : 'Progress'});


    var lostwood_content = "<p>" + (grouse_lostwood_validated / 3600).toFixed(1) + " hours watched</p>" +
                           "<p>" + (grouse_lostwood_processed / 3600).toFixed(1) + " hours available</p>" +
                           "<p>" + (grouse_lostwood_total / 3600).toFixed(1) + " hours total</p>";

    $('#grouse_lostwood_progress').popover({ placement : 'bottom', html : true,  content : lostwood_content, title : 'Progress'});


    var least_tern_content = "<p>" + (least_tern_validated / 3600).toFixed(1) + " hours watched</p>" +
                           "<p>" + (least_tern_processed / 3600).toFixed(1) + " hours available</p>" +
                           "<p>" + (least_tern_total / 3600).toFixed(1) + " hours total</p>";

    $('#least_tern_progress').popover({ placement : 'bottom', html : true,  content : least_tern_content, title : 'Progress'});


    var piping_plover_content = "<p>" + (piping_plover_validated / 3600).toFixed(1) + " hours watched</p>" +
                           "<p>" + (piping_plover_processed / 3600).toFixed(1) + " hours available</p>" +
                           "<p>" + (piping_plover_total / 3600).toFixed(1) + " hours total</p>";

    $('#piping_plover_progress').popover({ placement : 'bottom', html : true,  content : piping_plover_content, title : 'Progress'});
});

