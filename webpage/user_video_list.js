
$(document).ready(function () {
    var filters = {};
    var sort_by = 'filename';

    var all_users = false;
    var show_hidden = false;
    if (typeof is_instructional !== 'undefined') {
        all_users = true;
        filters['report_status'] = 'REVIEWED';
        filters['instructional'] = true;
        sort_by = 'recently viewed';
    }

    $('#show-all-videos-button').click(function() {
        all_users = !all_users;
        reload_videos();
    });

    $('#show-hidden-videos-button').click(function() {
        show_hidden = !show_hidden;
        reload_videos();
    });


    $('.filter-dropdown').click(function() {
        var filter_name = $(this).attr("filter_name");
        var filter_value = $(this).attr("filter_value");
        var dropdown_text = $(this).attr("dropdown_text");

        if (filters[filter_name] !== filter_value) {
            var dropdown_id = $(this).attr("dropdown_id");

            if (filter_value !== 'null') {
                $("#" + dropdown_id).addClass("btn-primary");
                filters[filter_name] = filter_value;
            } else {
                $("#" + dropdown_id).removeClass("btn-primary");
                delete filters[filter_name];
            }

            $("#" + dropdown_id).html(dropdown_text + " <span class='caret'></span>");

            console.log( JSON.stringify(filters) );
            reload_videos();
        }
    });

    var video_min = 0;
    var video_count = 15;

    reload_videos();

    function reload_videos(reset_video_min) {
        if (reset_video_min === undefined) video_min = 0;

        var submission_data = {
                                all_users : all_users,
                                show_hidden : show_hidden,
                                video_min : video_min,
                                video_count : video_count,
                                filters : filters,
                                sort_by : sort_by
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

                $('.toggle-observation-visibility-button').button();
                $('.toggle-observation-visibility-button').click(function() {
                    var observation_id = $(this).attr("observation_id");
                    var div_id = "#" + $(this).attr('id');
//                    console.log("clicked toggle observaiton visibility button, observation_id = " + observation_id);

                    $.ajax({
                        type: 'POST',
                        url: './toggle_observation_visibility.php',
                        data : { observation_id : observation_id },
                        dataType : 'JSON',
                        success : function(response) {
//                            console.log("response: " + JSON.stringify(response));

                            if (response['hidden'] === '1') {
                                $(div_id).html('show');
                                $(div_id).removeClass('btn-default');
                                $(div_id).addClass('btn-primary');
                            } else {
                                $(div_id).html('hide');
                                $(div_id).removeClass('btn-primary');
                                $(div_id).addClass('btn-default');
                            }
                        },
                        async: true
                    });
                });

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

                $(".report-observations-button").button();
                $(".report-observations-button").click(function() {
                    var video_segment_id = $(this).attr("video_segment_id");
                    console.log("video segment 2 id is: " + video_segment_id);

                    var text = "<p>Please enter a description of why you are reporting this video. Credit issues are not a valid reason for a report, please post in our forums in those cases.</p>" +
                               "<textarea style='width:97%;' rows=5 class='report-comments' id='report-comments-" + video_segment_id + "' video_segment_id=" + video_segment_id + "></textarea>" +
                               "<div class='btn btn-primary disabled pull-right report-final' style='margin-right:4px;' video_segment_id=" + video_segment_id + " id='report-final-" + video_segment_id + "'>Submit Report</div>";
                    $("#report-placeholder-" + video_segment_id).html(text);

                    $(".report-comments").bind('input propertychange', function() {
                        var video_segment_id = $(this).attr("video_segment_id");
                        if (this.value.length) {
                            $("#report-final-" + video_segment_id).removeClass("disabled");
                        } else {
                            $("#report-final-" + video_segment_id).addClass("disabled");
                        }
                    });

                    $(".report-final").button();
                    $(".report-final").click(function() {
                        if (!$(this).hasClass('disabled')) {
                            $(this).addClass('disabled');
                            var video_segment_id = $(this).attr("video_segment_id");
                            var comments = $("#report-comments-" + video_segment_id).val();
                            console.log("logging report for video " + video_segment_id + " with comments: '" + comments + "'");

                            $("#report-observations-" + video_segment_id).replaceWith("<button class='btn btn-warning disabled span6 pull-left' style='margin-top:0px;'>Pending Review</button>");

                            var text = "<p>This video was reported by " + user_name + " with the following description:</p>" +
                                       "<textarea readonly style='width:97%;' rows=5 class='report-comments' id='report-comments-" + video_segment_id + "' video_segment_id=" + video_segment_id + ">" + comments + "</textarea>";
                            $("#report-placeholder-" + video_segment_id).html(text);

                            $.ajax({
                                type: 'POST',
                                url: './report_video_segment.php',
                                data : { 
                                    report_comments : comments,
                                    video_segment_id : video_segment_id
                                },
                                dataType : 'json',
                                success : function(response) {
                                    console.log("successfully reported video");
                                },
                                error : function(jqXHR, textStatus, errorThrown) {
                                    alert(errorThrown);
                                },
                                async: true
                            });

                        }
                    });
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

        $("#go-to-textbox").keyup(function (e) {
            if (e.keyCode == 13) {
                var value = $('#go-to-textbox').val();

                if (Math.floor(value) == value && $.isNumeric(value)) {
    //                console.log("value is an integer!: " + value);
                    if (video_min != value) {
                        video_min = value;
                        video_min = video_min - (video_min % video_count);
                        reload_videos(false);
                    }
                }
            }
        });

        $('#display-1-dropdown').click(function(ev) {
            if (video_count != 1) {
                video_count = 1;
                reload_videos(false);
            }

            ev.preventDefault();
            ev.stopPropagation();
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

        $('#display-15-dropdown').click(function(ev) {
            if (video_count != 15) {
                video_count = 15;
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

        $('.sort-by-dropdown').click(function(ev) {
            var value = $(this).attr("sort_value");

            if (value !== sort_by) {
                sort_by = value;
                reload_videos(false);
            }

            ev.preventDefault();
            ev.stopPropagation();
        });
    }

});



