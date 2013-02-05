
$(document).ready(function () {
    $('#fast_forward_button').button();
    $('#fast_backward_button').button();


    $('#fast_backward_button').click(function() {
        var video = $('#wildlife_video').get(0);
//        console.log("clicking fast backward!, playback rate: " + video.playbackRate);

        video.playbackRate -= 2.0;
        $('#speed_textbox').val("speed:" + video.playbackRate);
    });


    $('#fast_forward_button').click(function() {
        var video = $('#wildlife_video').get(0);
//        console.log("clicking fast forward!, playback rate: " + video.playbackRate);

        video.playbackRate += 2.0;

        $('#speed_textbox').val("speed: " + video.playbackRate);
    });
});

