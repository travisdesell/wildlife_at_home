
$(document).ready(function () {
    $('#fast_forward_button').button();
    $('#fast_backward_button').button();


    $('#fast_backward_button').click(function() {
        var video = $('#wildlife_video').get(0);
        var rate = video.playbackRate;
        
        rate -= 2.0;
        if (rate < -9.0) rate = -9.0;

        video.playbackRate = rate;

//        console.log("clicking fast backward!, playback rate: " + video.playbackRate);

        $('#speed_textbox').val("speed:" + video.playbackRate);
    });


    $('#fast_forward_button').click(function() {
        var video = $('#wildlife_video').get(0);
        var rate = video.playbackRate;

        rate += 2.0;
        if (rate > 9.0) rate = 9.0;

        video.playbackRate = rate;

//        console.log("clicking fast forward!, playback rate: " + video.playbackRate);

        $('#speed_textbox').val("speed: " + video.playbackRate);
    });
});

