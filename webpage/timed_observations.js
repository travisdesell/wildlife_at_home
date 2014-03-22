function pad2(value) {
    var s = "00" + parseInt(Math.floor(value), 10);
    return s.substr(-2);
}

function isInt(value) {
    return typeof(value) == "number" && Math.floor(value) == value;
}

var comments_default = 'Insert comments and hashtags here.';

var tag_dropdowns;

function submit_observations(video_id, random) {
    $('.random-video-button').addClass("disabled");
    $('.next-video-button').addClass("disabled");

    var submission_data = {
            species_id : species_id,
            location_id : location_id,
            video_id : video_id,
            random : random
    };

    $.ajax({
        type: 'POST',
        url: './watch_interface/next_video.php',
        data : submission_data,
        dataType : 'json',
        success : function(response) {
            window.location.reload();
        },
        error : function(jqXHR, textStatus, errorThrown) {
            alert(errorThrown);
        },
        async: true
    });
}

function enable_next_video_buttons() {
    $('#random-video-button:not(.bound)').addClass('bound').click(function() {
        var video_id = $(this).attr("video_id");
        submit_observations(video_id, true);
    });

    $('#next-video-button:not(.bound)').addClass('bound').click(function() {
        var video_id = $(this).attr("video_id");
        submit_observations(video_id, false);
    });

    $('#finished-modal').on('hidden', function () {
        window.location.reload();
    });
}

function initialize_speed_buttons() {
    $('.fast-backward-button').click(function() {
        var video_id = $(this).attr('video_id');
        var video = $('#wildlife-video-' + video_id).get(0);
        var rate = video.playbackRate;

        if (rate === -16.0)         rate = -16.0;
        else if (rate === -12.0)    rate = -16.0; 
        else if (rate === -10.0)    rate = -12.0;
        else if (rate === -8.0)     rate = -10.0;
        else if (rate === -6.0)     rate = -8.0;
        else if (rate === -4.0)     rate = -6.0;
        else if (rate === -2.0)     rate = -4.0;
        else if (rate === -1.0)     rate = -2.0;
        else if (rate === 1.0)      rate = -1.0;
        else if (rate === 2.0)      rate = 1.0; 
        else if (rate === 4.0)      rate = 2.0;
        else if (rate === 6.0)      rate = 4.0;
        else if (rate === 8.0)      rate = 6.0;
        else if (rate === 10.0)     rate = 8.0;
        else if (rate === 12.0)     rate = 10.0;
        else if (rate === 16.0)     rate = 12.0;
        else rate = -1.0;

        video.playbackRate = rate;

        //console.log("clicking fast backward!, playback rate: " + video.playbackRate);

        $('#speed-textbox-' + video_id).val("speed:" + video.playbackRate);
    });

    $('.fast-forward-button').click(function() {
        var video_id = $(this).attr('video_id');
        var video = $('#wildlife-video-' + video_id).get(0);
        var rate = video.playbackRate;

        if (rate === -16.0)         rate = -12.0;
        else if (rate === -12.0)    rate = -10.0; 
        else if (rate === -10.0)    rate = -8.0;
        else if (rate === -8.0)     rate = -6.0;
        else if (rate === -6.0)     rate = -4.0;
        else if (rate === -4.0)     rate = -2.0;
        else if (rate === -2.0)     rate = -1.0;
        else if (rate === -1.0)     rate = 1.0;
        else if (rate === 1.0)      rate = 2.0;
        else if (rate === 2.0)      rate = 4.0; 
        else if (rate === 4.0)      rate = 6.0;
        else if (rate === 6.0)      rate = 8.0;
        else if (rate === 8.0)      rate = 10.0;
        else if (rate === 10.0)     rate = 12.0;
        else if (rate === 12.0)     rate = 16.0;
        else if (rate === 16.0)     rate = 16.0;
        else rate = 1.0;

        video.playbackRate = rate;

        //console.log("clicking fast forward!, playback rate: " + video.playbackRate);

        $('#speed-textbox-' + video_id).val("speed:" + video.playbackRate);
    });
}


function initialize_event_list() {
    $('.event-list-div').each(function() {
        var video_id = $(this).attr("video_id");

        $.ajax({
            type: 'POST',
            url: './watch_interface/get_timed_observations.php',
            data : {video_id : video_id},
            dataType : 'json',
            success : function(response) {
                $("#event-list-div-" + video_id).html( response['html'] );

                enable_observation_table();
                initialize_speed_buttons();
            },
            error : function(jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            },
            async: true
        });

        $.ajax({
            type: 'POST',
            url: './watch_interface/get_tag_dropdowns.php',
            data : {},
            dataType : 'json',
            success : function(response) {
                tag_dropdowns = response;
//                console.log("get_tag_dropdowns response was : '" + JSON.stringify(response));
//                $("#event-list-div-" + video_id).html( response['html'] );

//                enable_observation_table();
            },
            error : function(jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            },
            async: true
        });
     });

}

function enable_observation_table() {
    $('.dropdown-dropup:not(.dropdownup_bound)').addClass('dropdownup_bound').click(function() {
        var distance = ($(window).scrollTop() + $(window).height()) - $(this).offset().top;
        console.log ("distance: " + distance);

        if ( distance < 270 ) {
            $("#event-dropdown-group-" + $(this).attr("id_num")).addClass('dropup');
        } else {
            $("#event-dropdown-group-" + $(this).attr("id_num")).removeClass('dropup');
        }
    });

    $('.event-dropdown:not(.bound)').addClass('bound').click(function(ev) {
        var event_id = $(this).attr("event_id");
        var event_text = $(this).attr("event_text");
        var video_id = $(this).attr("video_id");
        var observation_id = $(this).attr("observation_id");

        //console.log("target = #event-button-" + video_id);
        //console.log("event_ids = " + JSON.stringify(event_ids));

        function toTitleCase(str) {
            return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
        }

        $('#event-button-' + observation_id).html(toTitleCase(event_text) + ' <span class="caret"></span>');
        $('#event-button-' + observation_id).attr("event_id", event_id);

//            console.log("setting tags row to: " + tag_dropdowns[event_id].replace(/#observation_id#/g, observation_id) );
        if (event_id in tag_dropdowns) {
            $('#observation-tags-row-' + observation_id).html( tag_dropdowns[event_id].replace(/#observation_id#/g, observation_id) );
            update_tag_dropdowns();
            update_tags();
        }
        update_observation(observation_id, video_id);

        ev.preventDefault();
    });

    function update_tag_dropdowns() {
        $('.tag-dropdown:not(.bound)').addClass('bound').click(function(ev) {
            var tag_id = $(this).attr("tag_id");
            var tag_text = $(this).attr("tag_text");
            var observation_id = $(this).attr("observation_id");

            var video_id = $(this).closest('table').attr("video_id");
            var badge_html = "<span style='margin:3px; height:16px;' class='badge tag-element' tag_text='" + tag_text + "' video_id=" + video_id + " observation_id=" + observation_id + ">" + tag_text + "</span>";

            console.log("appending badge html: " + badge_html);

            if( $("#tag-div-" + observation_id).html().indexOf(tag_text + "</span>") === -1 ) {
                $("#tag-div-" + observation_id).append(badge_html);

                 update_tags();
            }

            update_observation(observation_id, video_id);
            ev.preventDefault();
        });
    }

    update_tag_dropdowns();

    function update_tags() {
        $('.tag-element:not(.over-bound)').addClass('over-bound').mouseover(function(ev) {
            var tag_text = $(this).attr("tag_text");
//                console.log("mouse is over: '" + tag_text + "'");
            if ( !$(this).hasClass("has-remove-icon") ) {
                $(this).addClass("has-remove-icon");
                $(this).append(" <i class='icon-white icon-remove-sign'></i>");
            }
        });

        $('.tag-element:not(.leave-bound)').addClass('leave-bound').mouseleave(function(ev) {
            var tag_text = $(this).attr("tag_text");
//                console.log("mouse is over: '" + tag_text + "'");
            $(this).text(tag_text);
            $(this).removeClass("has-remove-icon");
        });

        $('.tag-element:not(.click-bound)').addClass('click-bound').click(function(ev) {
            var tag_text = $(this).attr("tag_text");
            var observation_id = $(this).attr("observation_id");
//                console.log("mouse is over: '" + tag_text + "'");
            $(this).remove();

            var video_id = $(this).attr("video_id");
            console.log("updating observation from tag click with video id: " + video_id);
            update_observation(observation_id, video_id);
        });
    }

    update_tags();


    $('.comments-textarea:not(.bound1)').addClass('bound1').click(function() {
            if ($(this).val() == comments_default) {
                $(this).removeClass('default_comments_text');
                $(this).val('');
            }
    });

    $('.comments-textarea:not(.bound2)').addClass('bound2').each(function() {
            if ($(this).val() == '') {
                $(this).addClass('default_comments_text');
                $(this).val(comments_default);
            }
    });

    $('.comments-textarea:not(.bound3)').addClass('bound3').blur(function() {
            if ($(this).val() == '') {
                $(this).addClass('default_comments_text');
                $(this).val(comments_default);
            }
    });

    $('.comments-textarea:not(.bound-change)').addClass('bound-change').change(function() {
        var observation_id = $(this).attr("observation_id");
        var video_id = $(this).attr("video_id");
//            console.log("CHANGE comments textarea with id: " + observation_id);

        update_observation(observation_id, video_id);
    });

    function convert_to_date(video_id, current_time) {
        var video_start_time = $("#wildlife-video-" + video_id).attr("start_time");
        var time = current_time * 1000;

//            console.log("video start time: " + video_start_time);

        //convert the mysql datetime to a javascript Date object
        var t = video_start_time.split(/[- :]/);
        var video_date = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
        /*
        console.log("video_date: " + video_date);
        console.log("time: " + time);

        console.log("year:  " + video_date.getFullYear());
        console.log("month: " + video_date.getMonth());
        console.log("date:   " + video_date.getDate());
        */

        var current_time = new Date(video_date.getTime() + time);

        /*
        console.log("new year:  " + current_time.getFullYear());
        console.log("new month: " + (current_time.getMonth() + 1));
        console.log("new date:   " + current_time.getDate());
        */

        var year = current_time.getFullYear();
        var month = current_time.getMonth() + 1;
        var day = current_time.getDate();
        var hours = current_time.getHours();
        var minutes = current_time.getMinutes();
        var seconds = current_time.getSeconds();
        var result = year + "-" + month + "-" + day + " " + (hours < 10 ? "0" + hours : hours) + ":" + (minutes < 10 ? "0" + minutes : minutes) + ":" + (seconds  < 10 ? "0" + seconds : seconds);

//            console.log( "Time: " + result);
        return result;
    }

    $('.time-textarea:not(.bound)').addClass('bound').click(function() {
        var observation_id = $(this).attr("observation_id");
        var video_id = $(this).attr("video_id");

        if ($(this).hasClass('default_time_text')) {
            var result = $("#wildlife-video-" + video_id).get(0).currentTime;
            $(this).attr("time_s", result);
            $(this).attr("time_date", convert_to_date(video_id, result));

            result_text = pad2(result / 3600) + ":" + pad2((result % 3600) / 60) + ":" + pad2(result % 60);

            $(this).val( result_text );
            update_observation(observation_id, video_id);
        }

        $(this).removeClass('default_time_text');
    });

    var start_time_default = "Click for start time.";
    var end_time_default = "Click for end time.";

    $('.time-textarea:not(.bound2)').addClass('bound2').each(function() {
            if ($(this).hasClass("start-time-textarea")) {
                time_default = start_time_default;
            } else {
                time_default = end_time_default;
            }

            if ($(this).val() == '-1') {
                $(this).addClass('default_time_text');
                $(this).val(time_default);
                $(this).attr("time_s", '-1');
                $(this).attr("time_date", '');
            }
    });

    $('.time-textarea:not(.bound3)').addClass('bound3').blur(function() {
            if ($(this).hasClass("start-time-textarea")) {
                time_default = start_time_default;
            } else {
                time_default = end_time_default;
            }

            if ($(this).val() == '-1') {
                $(this).addClass('default_time_text');
                $(this).val(time_default);
                $(this).attr("time_s", '-1');
                $(this).attr("time_date", '');
            }
    });

    $('.time-textarea:not(.bound-change)').addClass('bound-change').change(function() {
        var observation_id = $(this).attr("observation_id");
        var video_id = $(this).attr("video_id");
        //console.log("CHANGE time textarea with id: " + observation_id);
        //console.log("this.val(): " + $(this).val());

        if ( $(this).val() =='' ) {
            $(this).val('-1');
            $(this).addClass('default_time_text');
            $(this).attr("time_s", '-1');
            $(this).attr("time_date", '');
        } else {
            var t = $(this).val().split(/[- :]/);
            /*
            console.log("t.length: " + t.length);
            console.log("t[0]: " + t[0]);
            console.log("t[1]: " + t[1]);
            console.log("t[2]: " + t[2]);
            console.log("!isNaN(t[0]): " + !isNaN(t[0]));
            console.log("!isNaN(t[1]): " + !isNaN(t[1]));
            console.log("!isNaN(t[2]): " + !isNaN(t[2]));
            */

            if (t.length == 3 && !isNaN(t[0]) && !isNaN(t[1]) && !isNaN(t[2])) {
                var time_in_seconds = Number(t[0] * 3600) + Number(t[1] * 60) + Number(t[2]);
                    console.log($(this).val() + " to seconds: " + time_in_seconds);
                $(this).attr("time_s", time_in_seconds);
                $(this).attr("time_date", convert_to_date(video_id, time_in_seconds));

            } else {
                $(this).val('-1');
                $(this).addClass('default_time_text');
                $(this).attr("time_s", '-1');
                $(this).attr("time_date", '');
            }
        }

        update_observation(observation_id, video_id);
    });



    $('.new-observation-button:not(.bound)').addClass('bound').click(function() {
        if ($(this).hasClass("disabled")) return;

        var video_id = $(this).attr("video_id");
        var div_id = "#new-observation-button-" + video_id;
        $(this).addClass("disabled");

        $(".total_events").text( parseInt($(".total_events").text(), 10) + 1 );

        var reb_text = $("#recorded-event-button-" + video_id).text();
        reb_text = (parseInt( reb_text.substring(0, reb_text.indexOf(" ")), 10) + 1);
        if (reb_text === 1) reb_text += " recorded event";
        else reb_text += " recorded events";
        $("#recorded-event-button-" + video_id).text( reb_text );

        var submission_data = {
                                video_id : video_id,
                                event_id : 0,
                                start_time : '',
                                end_time : '',
                                comments : '',
                                tags : ''
                              };

        $.ajax({
            type: 'POST',
            url: './watch_interface/new_timed_observation.php',
            data : submission_data,
            dataType : 'json',
            success : function(response) {
                $(div_id).removeClass("disabled");

                $('#event-list-div-' + video_id).append( response['html'] );
                enable_observation_table();
            },
            error : function(jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            },
            async: true
        });
    });


    $('.remove-observation-button:not(.bound)').addClass('bound').click(function() {
        var observation_id = $(this).attr('observation_id');
        var video_id = $(this).attr('video_id');
        var div_id = "#remove-observation-button-" + observation_id;

        $(this).addClass("disabled");

        $(".total_events").text( $(".total_events").text() - 1 );

        var reb_text = $("#recorded-event-button-" + video_id).text();
        reb_text = (parseInt( reb_text.substring(0, reb_text.indexOf(" ")), 10) - 1);
        if (reb_text === 1) reb_text += " recorded event";
        else reb_text += " recorded events";
        $("#recorded-event-button-" + video_id).text( reb_text );

        var submission_data = {
                                observation_id : observation_id
                              };
        $.ajax({
            type: 'POST',
            url: './watch_interface/remove_timed_observation.php',
            data : submission_data,
            dataType : 'json',
            success : function(response) {
                $(div_id).removeClass("disabled");

                $("#observations-table-div-" + observation_id).remove();
            },
            error : function(jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            },
            async: true
        });
    });

    function update_observation(observation_id, video_id) {
        var comments = $("#comments-textarea-" + observation_id).val();
        if (comments == comments_default) comments = '';

        var tag_str = "";
        var first = true;
        $("#tag-div-" + observation_id + " .tag-element").each(function() {
            if (first) {
                first = false;
            } else {
                tag_str += "#";
            }
            tag_str += $(this).attr("tag_text");
            console.log("iterated over text: " + $(this).attr("tag_text"));
        });

//            console.log("tag_str: '" + tag_str + "'");

        var submission_data = {
                                observation_id : observation_id,
                                video_id : video_id,
                                event_id : $("#event-button-" + observation_id).attr("event_id"),
                                start_time_s : $("#start-time-textarea-" + observation_id).attr("time_s"),
                                end_time_s : $("#end-time-textarea-" + observation_id).attr("time_s"),
                                start_time : $("#start-time-textarea-" + observation_id).attr("time_date"),
                                end_time : $("#end-time-textarea-" + observation_id).attr("time_date"),
                                comments : comments,
                                tags : tag_str
                              };

        console.log("start_time:   " + submission_data['start_time']);
        console.log("end_time:     " + submission_data['end_time']);
        console.log("start_time_s: " + submission_data['start_time_s']);
        console.log("end_time_s:   " + submission_data['end_time_s']);

        $.ajax({
            type: 'POST',
            url: './watch_interface/update_timed_observation.php',
            data : submission_data,
            dataType : 'json',
            success : function(response) {

                $('#new-observation-button-' + video_id).removeClass('disabled');
                $('#remove-observation-button-' + observation_id).removeClass('disabled');

                $("#observations-table-div-" + observation_id).replaceWith( response['html'] );
                enable_observation_table();
            },
            error : function(jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            },
            async: true
        });
    }

    $('.discuss-observation-button:not(.click-bound)').addClass('click-bound').click(function() {
        var observation_id = $(this).attr("observation_id");
        var video_filename = $(this).closest('.event-list-div').attr("video_filename");
        var video_id = $(this).closest('table').attr("video_id");

        var start_time_s = Math.floor( $("#start-time-textarea-" + observation_id).attr("time_s") );
        var start_time_date = $("#start-time-textarea-" + observation_id).attr("time_date");
        var start_time_text = $("#start-time-textarea-" + observation_id).val();

        var end_time_s = Math.floor( $("#end-time-textarea-" + observation_id).attr("time_s") );
        var end_time_date = $("#end-time-textarea-" + observation_id).attr("time_date");
        var end_time_text = $("#end-time-textarea-" + observation_id).val();

        var comments = $("#comments-textarea-" + observation_id).val();

        if (comments == comments_default) comments = '';

        /*
        console.log("start_time_s: " + start_time_s);
        console.log("start_time_date: " + start_time_date);
        console.log("start_time_text: " + start_time_text);
        console.log("end_time_s: " + end_time_s);
        console.log("end_time_date: " + end_time_date);
        console.log("end_time_text: " + end_time_text);
        */

        var time_range = '#t=' + start_time_s + "," + end_time_s;

        var forum_post_content = "I would like to discuss the event between " + start_time_text + " (approximately " + start_time_date + ") and " + end_time_text + " (approximately " + end_time_date + ").\n\n[video_new='" + time_range +"']" + video_filename + "[/video_new]\n\n" + comments;

//                console.log("forum post: " + forum_post_content);

        $("#discuss-video-content-" + observation_id).val( forum_post_content );
        $("#discuss-video-form-" + observation_id).submit();
    });


    $('#help-button').click(function() {
        $('#instructions-modal').modal( {keyboard: true} );
    });


    $('.discuss-observation-button:not(.has-tooltip)').addClass('has-tooltip').tooltip({ 
                'delay' : { show : 500, hide : 100 },
                'placement' : 'auto left',
                'html': true,
                'title' : '<p align="left">You can click this button to discuss this event in the video discussion forums. You need to have specified a valid start and end time so the appropriate clip of the video can be shown.</p>'
            });

    $('.remove-observation-button:not(.has-tooltip)').addClass('has-tooltip').tooltip({ 
                'delay' : { show : 500, hide : 100 },
                'placement' : 'auto left',
                'html': true,
                'title' : '<p align="left">Clicking this button will remove this event.</p>'
            });

    $('.new-observation-button:not(.has-tooltip)').addClass('has-tooltip').tooltip({ 
                'delay' : { show : 500, hide : 100 },
                'placement' : 'auto bottom',
                'html': true,
                'title' : '<p align="left">This will add another event for this video. You can enter and modify multiple events at the same time.</p>'
            });

}




$(document).ready(function () {
//    console.log("start_time: " + start_time);
    initialize_event_list();
    initialize_speed_buttons();
    enable_observation_table();


    /*
    $('.random-video-button:not(.has-tooltip)').addClass('has-tooltip').tooltip({ 
                'delay' : { show : 500, hide : 100 },
                'placement' : 'top',
                'html': true,
                'title' : '<p align="left">Click this button to finish watching this video and make your events ready for validation. Your next video will be chosen randomly.</p>'
            });


    $('.next-video-button:not(.has-tooltip)').addClass('has-tooltip').tooltip({ 
                'delay' : { show : 500, hide : 100 },
                'placement' : 'top',
                'html': true,
                'title' : '<p align="left">Click this button to finish watching this video and make your events ready for validation. If possible, your next video will be the one after the current video.</p>'
            });
            */

    $('.skip-video-button:not(.bound)').addClass('bound').click(function() {
        $(this).addClass("disabled");
        $('.finished-video-button').addClass("disabled");

        var video_id = $(this).attr("video_id");

        $('#new-observation-button-' + video_id).addClass('disabled');

        var submission_data = {
                                video_id : video_id,
                                species_id : species_id,
                                location_id : location_id,
                              };

        $.ajax({
            type: 'POST',
            url: './watch_interface/skip_video.php',
            data : submission_data,
            dataType : 'json',
            success : function(response) {
                window.location.reload();
            },
            error : function(jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            },
            async: true
        });
    });

    $('.finished-video-button:not(.bound)').addClass('bound').click(function() {
        $('.skip-video-button').addClass("disabled");
        $('.finished-video-button').addClass("disabled");

        var video_id = $(this).attr("video_id");
        $('#new-observation-button-' + video_id).addClass('disabled');


        var submission_data = {
                                species_id : species_id,
                                location_id : location_id,
                                video_id : video_id
                              };

        $.ajax({
            type: 'POST',
            url: './watch_interface/finished_video.php',
            data : submission_data,
            dataType : 'json',
            success : function(response) {
                console.log("GOT RESPONSE!: " + response['html']);

                $('#finished-modal').html( response['html'] );
                enable_next_video_buttons();

                $('#finished-modal').modal( {keyboard: false} )
            },
            error : function(jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            },
            async: true
        });

    });

    $('.difficulty-dropdown:not(.bound)').addClass('bound').click(function() {
        var video_id = $(this).closest(".btn-group").attr("video_id");
        var difficulty = $(this).attr("difficulty");
        console.log("VIDEO ID IS: " + video_id  + ", difficulty: " + difficulty);

        var target_button = $(this).closest(".btn-group").find(".btn");
        target_button.addClass("disabled");
        target_button.html( "Difficulty: " + $(this).text() + "<span class='caret'></span>" );
        if (difficulty === 'easy') {
            target_button.addClass("btn-success");
            target_button.removeClass("btn-warning");
            target_button.removeClass("btn-danger");
        } else if (difficulty === 'medium') {
            target_button.removeClass("btn-success");
            target_button.addClass("btn-warning");
            target_button.removeClass("btn-danger");
        } else if (difficulty === 'hard') {
            target_button.removeClass("btn-success");
            target_button.removeClass("btn-warning");
            target_button.addClass("btn-danger");
        }

        var submission_data = {
                                species_id : species_id,
                                location_id : location_id,
                                video_id : video_id,
                                difficulty : difficulty
                              };

        $.ajax({
            type: 'POST',
            url: './watch_interface/update_difficulty.php',
            data : submission_data,
            dataType : 'json',
            success : function(response) {
                target_button.removeClass("disabled");
            },
            error : function(jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            },
            async: true
        });
    });
});

