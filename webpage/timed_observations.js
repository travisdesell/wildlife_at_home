function pad2(value) {
    var s = "00" + parseInt(Math.floor(value), 10);
    return s.substr(-2);
}

function isInt(value) {
    return typeof(value) == "number" && Math.floor(value) == value;
}

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
//    console.log("initializing speed buttons!");

    $('.fast-backward-button:not(.bound)').addClass('bound').click(function() {
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
        else if (rate === 5.0)      rate = 4.0;
        else if (rate === 6.0)      rate = 4.0;
        else if (rate === 8.0)      rate = 6.0;
        else if (rate === 10.0)     rate = 8.0;
        else if (rate === 12.0)     rate = 10.0;
        else if (rate === 16.0)     rate = 12.0;
        else rate = -1.0;

        console.log("clicking fast backward!, attempting rate: " + rate);

        video.playbackRate = rate;

        console.log("clicking fast backward!, playback rate: " + video.playbackRate);

        $('#speed-textbox-' + video_id).val("speed:" + video.playbackRate);
    });

    $('.fast-forward-button:not(.bound)').addClass('bound').click(function() {
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
        else if (rate === 5.0)      rate = 6.0;
        else if (rate === 6.0)      rate = 8.0;
        else if (rate === 8.0)      rate = 10.0;
        else if (rate === 10.0)     rate = 12.0;
        else if (rate === 12.0)     rate = 16.0;
        else if (rate === 16.0)     rate = 16.0;
        else rate = 1.0;

        console.log("clicking fast forward!, attempting rate: " + rate);

        video.playbackRate = rate;

        console.log("clicking fast forward!, playback rate: " + video.playbackRate);

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

function initialize_bgsub_timeline() {
    $('.event-list-div').each(function() {
        var video_id = $(this).attr("video_id");

        $.ajax({
            type: 'GET',
            url: './video_computed_event_times.php',
            //data : {video_id : 59040},
            data : {video_id : video_id},
            dataType : 'json',
            success : function(response) {
                if (response.length > 0) {
                    var central_offset = 5*60*60000;
                    var local_offset = new Date().getTimezoneOffset()*60000;
                    for (var i in response) {
                        response[i][1] = new Date((response[i][1] * 1000)-central_offset+local_offset);
                        response[i][2] = new Date((response[i][2] * 1000)-central_offset+local_offset);
                    }
                    google.setOnLoadCallback(drawWatchTimeline(video_id, response));
                }
            },
            error : function(jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            },
            async: true
        });
     });
}

function check_observations() {
    /**
     *      if there are no events, disabled the button
     *      If all button dropdown event_ids != 0
     *      and all time-textareas time_s != -1
     *      enable finished button
     */
    var input_data_valid = true;
    if ($(".event-dropdown-button").length == 0) input_data_valid = false;

    $('.time-textarea').each(function() {
        //console.log( "time_s: " + $(this).attr("time_s") );
        if ($(this).attr("time_s") == -1) input_data_valid = false;
    });

    $('.event-dropdown-button').each(function() {
        //console.log( "event id: " + $(this).attr("event_id") );
        if ($(this).attr("event_id") == 0) input_data_valid = false;
    });

    //console.log("UPDATING OBSERVATIONS AND INPUT DATA IS: " + input_data_valid);

    if (input_data_valid) {
        $(".finished-video-button").removeClass("disabled");
    } else {
        $(".finished-video-button").addClass("disabled");
    }
}


function enable_observation_table() {
//    console.log("allow_add_removal: '" + allow_add_removal + "'");
    if (allow_add_removal == 0) {
        $(".new-observation-button").addClass("disabled");
        $(".new-observation-button").hide();
        $(".remove-observation-button").addClass("disabled");
        $(".remove-observation-button").hide();
    }
    check_observations();

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

//        console.log("removing tag elements");
        //Remove the tags when changing the event
        $('#observation-tags-row-' + observation_id).find(".tag-element").remove();

//        console.log("observation tags row before update_observation: " + $("#observation-tags-row-" + observation_id).html());

        update_observation(observation_id, video_id);

        ev.preventDefault();
    });

    function update_tag_dropdowns() {
        $('.tag-dropdown:not(.bound)').addClass('bound').click(function(ev) {
            var tag_id = $(this).attr("tag_id");
            var tag_text = $(this).attr("tag_text");
            var observation_id = $(this).attr("observation_id");

            var video_id = $(this).closest('table').attr("video_id");
            var badge_html = "<span style='margin:3px; height:20px;' class='badge tag-element' tag_text='" + tag_text + "' video_id=" + video_id + " observation_id=" + observation_id + ">" + tag_text + "</span>";

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
                $(this).append(" <span class='glyphicon glyphicon-remove'></span>");
//                $(this).append(" <i class='icon-white icon-remove-sign'></i>");
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


    $('.comments-textarea:not(.bound-change)').addClass('bound-change').change(function() {
        var observation_id = $(this).attr("observation_id");
        var video_id = $(this).attr("video_id");
//            console.log("CHANGE comments textarea with id: " + observation_id);

        update_observation(observation_id, video_id);
    });

    function convert_to_date(video_id, current_time) {
//        console.log("video id: " + video_id + ", current_time: " + current_time);
        var video_start_time = $("#wildlife-video-" + video_id).attr("start_time");
        var time = current_time * 1000;

//        console.log("video start time: " + video_start_time);

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

        console.log( "Time: " + result);
        return result;
    }

    $('.time-textarea:not(.bound)').addClass('bound').click(function() {
        var observation_id = $(this).attr("observation_id");
        var video_id = $(this).attr("video_id");

        if ($(this).val() == '') {
            var result = $("#wildlife-video-" + video_id).get(0).currentTime;
            $(this).attr("time_s", result);
            $(this).attr("time_date", convert_to_date(video_id, result));

            console.log("set time date to: '" + $(this).attr("time_date") + "'");

            result_text = pad2(result / 3600) + ":" + pad2((result % 3600) / 60) + ":" + pad2(result % 60);

            $(this).val( result_text );
            update_observation(observation_id, video_id);
        }
    });

    $('.time-textarea:not(.bound-change)').addClass('bound-change').change(function() {
        var observation_id = $(this).attr("observation_id");
        var video_id = $(this).attr("video_id");
        console.log("CHANGE time textarea with id: " + observation_id);
        //console.log("this.val(): " + $(this).val());

        if ( $(this).val() =='' ) {
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
                $(this).val('');
                $(this).addClass('default_time_text');
                $(this).attr("time_s", '-1');
                $(this).attr("time_date", '');
            }
        }

        update_observation(observation_id, video_id);
    });

    $('.revalidate-events-button:not(.bound)').addClass('bound').click(function() {
        if ($(this).hasClass("disabled")) return;

        var video_id = $(this).attr("video_id");

        console.log("need to revalidate video: " + video_id);

        var revalidate_button = $(this);
        revalidate_button.addClass("disabled");

        var submission_data = {
                                video_id : video_id 
                              };
        $.ajax({
            type: 'POST',
            url: './watch_interface/revalidate_video.php',
            data : submission_data,
            dataType : 'json',
            success : function(response) {
                revalidate_button.text("Revalidation Pending");
            },
            error : function(jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            },
            async: true
        });
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

                var observation_id = response['observation_id'];

                var all_obs_body = $(".all-observations-table[video_id=" + video_id + "] tbody");

//                console.log("body html: " + all_obs_body.html());

                var added = false;
                var i = 0;

                for (; i < all_obs_body.children().length; i++) {
                    // console.log("comparing '" + user_name + "' to '" + all_obs_body.children().eq(i).children().eq(0).text() + "'");
                    if (user_name == all_obs_body.children().eq(i).children().eq(0).text()) {
                        added = true;

                        // console.log("ADDING BEFORE body row[" + i + "]: " + all_obs_body.children().eq(i).children().eq(0).text() );

                        all_obs_body.children().eq(i).before(
                            "<tr>" +
                            "<td>" + user_name + "</td> " + //user name
                            "<td>-</td> " + //event 
                            "<td>0000-00-00 00:00:00</td> " + //start time
                            "<td>0000-00-00 00:00:00</td> " + //end time
                            "<td></td> " + //comments
                            "<td></td>" + //tags
                            "<td>UNVALIDATED</td>" + //status
                            //report button
                            "<td style='text-align:center;'> <button class='btn btn-small btn-danger pull-center report-observation-button rob-" + video_id + " bound' observation_id='" + observation_id + "' video_id='" + video_id + "' style='margin-top:2px; margin-bottom:2px; padding:0px; width:25px;' report_comments_text='' report_status='UNREPORTED' reporter_name='' response_comments_text='' responder_name=''><span class='glyphicon glyphicon-question-sign'</button> </td>" +
                            "</tr>");

                        break;
                    }
                }

                if (!added) {
                    all_obs_body.children().eq(i-1).after(
                        "<tr>" +
                        "<td>" + user_name + "</td> " + //user name
                        "<td>-</td> " + //event 
                        "<td>0000-00-00 00:00:00</td> " + //start time
                        "<td>0000-00-00 00:00:00</td> " + //end time
                        "<td></td> " + //comments
                        "<td></td>" + //tags
                        "<td>UNVALIDATED</td>" + //status
                        //report button
                        "<td style='text-align:center;'> <button class='btn btn-small btn-danger pull-center report-observation-button rob-" + video_id + " bound' observation_id='" + observation_id + "' video_id='" + video_id + "' style='margin-top:2px; margin-bottom:2px; padding:0px; width:25px;' report_comments_text='' report_status='UNREPORTED' reporter_name='' response_comments_text='' responder_name=''><span class='glyphicon glyphicon-question-sign'</button> </td>" +
                        "</tr>");

                }

                enable_observation_table();

                var observations_count = $(".observations-table[video_id=" + video_id + "]").length;
                //console.log("observations count: " + observations_count);

            },
            error : function(jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            },
            async: true
        });
    });


    $('.remove-observation-button:not(.bound)').addClass('bound').click(function() {
        if ($(this).hasClass("disabled")) return;

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
                $(".report-observation-button[observation_id=" + observation_id + "]").closest("tr").remove();

                var observations_count = $(".observations-table[video_id=" + video_id + "]").length;
                //console.log("observations count: " + observations_count);
                check_observations();
            },
            error : function(jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            },
            async: true
        });
    });

    function update_observation(observation_id, video_id) {
        var comments = $("#comments-textarea-" + observation_id).val();

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

        console.log("tag_str: '" + tag_str + "'");

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

        $.ajax({
            type: 'POST',
            url: './watch_interface/update_timed_observation.php',
            data : submission_data,
            dataType : 'json',
            success : function(response) {

//                $('#new-observation-button-' + video_id).removeClass('disabled');
                $('#remove-observation-button-' + observation_id).removeClass('disabled');

                $("#observations-table-div-" + observation_id).replaceWith( response['html'] );
                enable_observation_table();

                /*
                console.log("start_time:   " + submission_data['start_time']);
                console.log("end_time:     " + submission_data['end_time']);
                console.log("start_time_s: " + submission_data['start_time_s']);
                console.log("end_time_s:   " + submission_data['end_time_s']);
                */

                var closest_tr = $(".report-observation-button[observation_id=" + observation_id + "]").closest("tr");
                /*
                console.log("td 0: '" + closest_tr.children().eq(0).text() + "'");
                console.log("td 1: '" + closest_tr.children().eq(1).text() + "'");
                console.log("td 2: '" + closest_tr.children().eq(2).text() + "'");
                console.log("event_id: " + submission_data['event_id'] + ", event text: " + $(".event-dropdown[event_id=" + submission_data['event_id'] + "]").attr("event_text"));
                */

                closest_tr.children().eq(1).text( $(".event-dropdown[event_id=" + submission_data['event_id'] + "]").attr("event_text") );
                closest_tr.children().eq(2).text(submission_data['start_time']);
                closest_tr.children().eq(3).text(submission_data['end_time']);
                closest_tr.children().eq(4).text(submission_data['comments']);
                closest_tr.children().eq(5).text( tag_str );

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
        $('#instructions-modal').modal( {keyboard: true, show: true} );
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


    var finished_video_id = $(".finished-video-button").attr("video_id");
    //console.log("finished_video_id: " + finished_video_id);
    if (finished_video_id) {
        var observations_count = $(".observations-table[video_id=" + finished_video_id + "]").length;
        //console.log("observations count: " + observations_count);
    }

}




$(document).ready(function () {
    //console.log("start_time: " + start_time);
    initialize_event_list();
    initialize_speed_buttons();
    initialize_bgsub_timeline();
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
        if ($(this).hasClass("disabled")) return;

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
                //console.log("GOT RESPONSE!: " + response['html']);

                $('#finished-modal').html( response['html'] );
                enable_next_video_buttons();

                $('#finished-modal').modal( {keyboard: false, show: true} )
                // Load Google Chart here
                $.ajax({
                    type: 'GET',
                    url: './video_event_times.php',
                    data : submission_data,
                    dataType : 'JSON',
                    success : function(response) {
                        for (var i in response) {
                            response[i][2] = getDate(response[i][2]);
                            response[i][3] = getDate(response[i][3]);
                        }
                        google.setOnLoadCallback(drawReviewTimeline(submission_data.video_id, response));
                    },
                    error : function(jqXHR, textStatus, errorThrown) {
                        alert(errorThrown);
                    },
                    async: true
                });
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

