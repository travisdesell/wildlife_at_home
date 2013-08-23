
$(document).ready(function () {

    var video_min = 0;
    var video_count = 5;

    var filters =   {};

    filters['report_status'] = 'REPORTED';

    reload_videos();

    function reload_videos(reset_video_min) {
        if (reset_video_min === undefined) video_min = 0;

        var submission_data = {
                                all_users : true,
                                video_min : video_min,
                                video_count : video_count,
                                filters : filters
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

                /**
                 * Use this to create a forum thread with the video.
                 */
                $('.discuss-video-button').button();
                $('.discuss-video-button').click(function() { 
                    var form_id = $(this).attr('id');
                    form_id = '#' + form_id;
                    form_id = form_id.replace('button', 'form');
                    console.log('form id is: ' + form_id);
                    $(form_id).submit();
                });
            },
            error : function(jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            },
            async: true
        });
    }

    function init_dropdown() {
        $('.video-nav-list').click(function(ev) {
                var new_min = $(this).attr("id");

                new_min = new_min.substring(11);

                if (video_min != new_min) {
                    video_min = new_min;
                    reload_videos(false);
                }

                ev.preventDefault();
                ev.stopPropagation();
        });

        $('#go-to-button').button();
        $('#go-to-button').click(function(ev) {
            var value = $('#go-to-textbox').val();

            if (Math.floor(value) == value && $.isNumeric(value)) {
//                console.log("value is an integer!: " + value);
                if (video_min != value) {
                    video_min = value;
                    video_min = video_min - (video_min % video_count);
                    reload_videos(false);
                }
            }
        });


        $('#display-5-dropdown').click(function(ev) {
            if (video_count != 5) {
                video_count = 5;
                reload_videos(false);
            }

            ev.preventDefault();
            ev.stopPropagation();
        });

        $('#display-10-dropdown').click(function(ev) {
            if (video_count != 10) {
                video_count = 10;
                reload_videos(false);
            }

            ev.preventDefault();
            ev.stopPropagation();
        });

        $('#display-20-dropdown').click(function(ev) {
            if (video_count != 20) {
                video_count = 20;
                reload_videos(false);
            }

            ev.preventDefault();
            ev.stopPropagation();
        });
    }

});
