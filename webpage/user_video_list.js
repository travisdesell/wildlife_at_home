
$(document).ready(function () {

    /**
     * Use this to create a forum thread with the video.
     */
    var discuss_video_clicked = false;
    $('#discuss-video-button').button();
    $('#discuss-video-button').click(function() { 
        discuss_video_clicked = true;
        $("#discuss-video-form").submit();
    });

    function remove_actives() {
        $('#invalid-nav-pill').removeClass('active');
        $('#interesting-nav-pill').removeClass('active');
        $('#bird-presence-nav-pill').removeClass('active');
        $('#chick-presence-nav-pill').removeClass('active');
        $('#predator-presence-nav-pill').removeClass('active');
        $('#nest-defense-nav-pill').removeClass('active');
        $('#nest-success-nav-pill').removeClass('active');
        $('#bird-leave-nav-pill').removeClass('active');
        $('#bird-return-nav-pill').removeClass('active');
    }

    var video_min = 0;
    var video_count = 5;
    var filter = '';

    function reload_videos() {
        var submission_data = {
                                filter : filter,
                                video_min : video_min,
                                video_count : video_count 
                              };

        $("#videos-placeholder").html("<div class=well well-large style='padding-top:15px'><div class='span12'><p>Loading videos...</p></div></div>");

        $.ajax({
            type: 'POST',
            url: './get_video_count_nav.php',
            data : submission_data,
            dataType : 'text',
            success : function(response) {
//                console.log("the response was:\n" + response);
                $("#videos-nav-placeholder").html(response);
                init_dropdown();
            },
            error : function(jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            },
            async: true
        });

        $.ajax({
            type: 'POST',
            url: './get_video_list.php',
            data : submission_data,
            dataType : 'text',
            success : function(response) {
//                console.log("the response was:\n" + response);
                $("#videos-placeholder").html(response);
            },
            error : function(jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            },
            async: true
        });
    }

    $('.nav-li').click(function() {
        remove_actives();
        $(this).addClass('active');

        filter = $(this).attr("id");

        reload_videos();
    });

    function init_dropdown() {
        $('.video-nav-list').click(function(ev) {
                var new_min = $(this).attr("id");

                new_min = new_min.substring(11);

                if (video_min != new_min) {
                    video_min = new_min;
                    reload_videos();
                }

                ev.preventDefault();
                ev.stopPropagation();
        });

        $('#display-5-dropdown').click(function(ev) {
            if (video_count != 5) {
                video_count = 5;
                reload_videos();
            }

            ev.preventDefault();
            ev.stopPropagation();
        });

        $('#display-10-dropdown').click(function(ev) {
            if (video_count != 10) {
                video_count = 10;
                reload_videos();
            }

            ev.preventDefault();
            ev.stopPropagation();
        });

        $('#display-20-dropdown').click(function(ev) {
            if (video_count != 20) {
                video_count = 20;
                reload_videos();
            }

            ev.preventDefault();
            ev.stopPropagation();
        });
    }

});



