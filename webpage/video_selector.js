
$(document).ready(function () {

    var belden_content = "<p>" + (grouse_belden_validated / 20) + " hours watched</p>" +
                           "<p>" + (grouse_belden_processed / 20) + " hours available</p>" +
                           "<p>" + (grouse_belden_total / 20) + " hours total</p>";

    $('#grouse_belden_progress').popover({ placement : 'bottom', html : true,  content : belden_content, title : 'Progress'});


    var blaisdell_content = "<p>" + (grouse_blaisdell_validated / 20) + " hours watched</p>" +
                           "<p>" + (grouse_blaisdell_processed / 20) + " hours available</p>" +
                           "<p>" + (grouse_blaisdell_total / 20) + " hours total</p>";

    $('#grouse_blaisdell_progress').popover({ placement : 'bottom', html : true,  content : blaisdell_content, title : 'Progress'});


    var lostwood_content = "<p>" + (grouse_lostwood_validated / 20) + " hours watched</p>" +
                           "<p>" + (grouse_lostwood_processed / 20) + " hours available</p>" +
                           "<p>" + (grouse_lostwood_total / 20) + " hours total</p>";

    $('#grouse_lostwood_progress').popover({ placement : 'bottom', html : true,  content : lostwood_content, title : 'Progress'});


    var least_tern_content = "<p>" + (least_tern_validated / 20) + " hours watched</p>" +
                           "<p>" + (least_tern_processed / 20) + " hours available</p>" +
                           "<p>" + (least_tern_total / 20) + " hours total</p>";

    $('#least_tern_progress').popover({ placement : 'bottom', html : true,  content : least_tern_content, title : 'Progress'});


    var piping_plover_content = "<p>" + (piping_plover_validated / 20) + " hours watched</p>" +
                           "<p>" + (piping_plover_processed / 20) + " hours available</p>" +
                           "<p>" + (piping_plover_total / 20) + " hours total</p>";

    $('#piping_plover_progress').popover({ placement : 'bottom', html : true,  content : piping_plover_content, title : 'Progress'});
});

