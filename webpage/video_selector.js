
$(document).ready(function () {

    var belden_content = "<p>" + grouse_belden_validated + " videos watched</p>" +
                           "<p>" + grouse_belden_available + " videos available</p>" +
                           "<p>" + grouse_belden_total + " videos total</p>";

    $('#grouse_belden_progress').popover({ placement : 'bottom', html : true,  content : belden_content, title : 'Progress'});


    var blaisdell_content = "<p>" + grouse_blaisdell_validated + " videos watched</p>" +
                           "<p>" + grouse_blaisdell_available + " videos available</p>" +
                           "<p>" + grouse_blaisdell_total + " videos total</p>";

    $('#grouse_blaisdell_progress').popover({ placement : 'bottom', html : true,  content : blaisdell_content, title : 'Progress'});


    var lostwood_content = "<p>" + grouse_lostwood_validated + " videos watched</p>" +
                           "<p>" + grouse_lostwood_available + " videos available</p>" +
                           "<p>" + grouse_lostwood_total + " videos total</p>";

    $('#grouse_lostwood_progress').popover({ placement : 'bottom', html : true,  content : lostwood_content, title : 'Progress'});


    var least_tern_content = "<p>" + least_tern_missouri_river_validated + " videos watched</p>" +
                           "<p>" + least_tern_missouri_river_available + " videos available</p>" +
                           "<p>" + least_tern_missouri_river_total + " videos total</p>";

    $('#least_tern_progress').popover({ placement : 'bottom', html : true,  content : least_tern_content, title : 'Progress'});


    var piping_plover_content = "<p>" + piping_plover_missouri_river_validated + " videos watched</p>" +
                           "<p>" + piping_plover_missouri_river_available + " videos available</p>" +
                           "<p>" + piping_plover_missouri_river_total + " videos total</p>";

    $('#piping_plover_progress').popover({ placement : 'bottom', html : true,  content : piping_plover_content, title : 'Progress'});
});

