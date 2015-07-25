$(document).ready(function () {

    /*
    var path = document.location.pathname;
    var dev_dir = path.substr(path.indexOf('/', 1) + 1, path.lastIndexOf('/') - path.indexOf('/', 1));

    console.log("path:    '" + path + "'");
    console.log("dev_dir: '" + dev_dir + "'");
    */

    var video_id_text = '';

    var video_filter_text = '';
    var event_filter_text = '';
    var video_min = 0;
    var video_count = 15;
    var showing_all_videos = true;

    load_videos();
    
    function load_videos() {
        var submission_data = {
                                showing_all_videos : showing_all_videos,
                                event_filter_text : event_filter_text,
                                video_filter_text : video_filter_text,
                                video_min : video_min,
                                video_count : video_count,
                                video_id_filter : video_id_text
                              };

        $.ajax({
            type: 'POST',
            url: './get_videos.php',
            data : submission_data,
            dataType : 'text',
            success : function(response) {
//                console.log("the response was:\n" + response);
                $("#video-list-placeholder").html(response);
                enable_panel();

                $(".private-video-button").button();
                $(".private-video-button").click(function() {
                    var is_private = $(this).attr('private');
                    var video_id = $(this).attr('video_id');
                    var div_id = "#" + $(this).attr('id');

                    $(div_id).addClass('disabled');
//                    console.log("is private? '" + is_private + "', video_id: '" + video_id + "'");
//                    console.log("div id: '" + div_id + "'");

                    $.ajax({
                        type: 'POST',
                        url: './expert_interface/toggle_private_video.php',
                        data : { is_private : is_private, video_id : video_id },
                        dataType : 'JSON',
                        success : function(response) {
//                            console.log("response: " + JSON.stringify(response));

                            if (response['is_private'] === 'true') {
                                $(div_id).attr('private', 'false');
                                $(div_id).html('public');
                                $(div_id).removeClass('btn-warning');
                                $(div_id).addClass('btn-success');
                            } else {
                                $(div_id).attr('private', 'true');
                                $(div_id).html('private');
                                $(div_id).addClass('btn-warning');
                                $(div_id).removeClass('btn-success');
                            }
                            $(div_id).removeClass('disabled');
                        },
                        async: true
                    });
                    
                });

                $(".tag-video-button").button();
                $(".tag-video-button").click(function() {
                    var video_id = $(this).attr('video_id');
                    var video_button = $(this);

                    console.log("clicked tag video button");

                    $.ajax({
                        type: 'POST',
                        url: './expert_interface/toggle_expert_flag.php',
                        data : { video_id : video_id },
                        dataType : 'JSON',
                        success : function(response) {
                            console.log("response: " + JSON.stringify(response));
                            if (response['expert_finished'] === 'FINISHED') {
                                video_button.removeClass("btn-primary");
                                video_button.addClass("btn-success");
                            } else if (response['expert_finished'] === 'WATCHED') {
                                video_button.removeClass("btn-success");
                                video_button.addClass("btn-primary");
                            } else {
                                video_button.removeClass("btn-success");
                                video_button.removeClass("btn-primary");
                            }

                        },
                        async: true

                    });
                });
            },
            error : function(jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            },
            async: true
        });

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
    }


    function enable_panel() {
        $('.panel-toggle').click(function(ev) {
            console.log("clicked an panel toggle with href: " + $(this).attr('href'));

            if ($( $(this).attr('href') + "_inner" ).html().indexOf('uninitialized') != -1) {
                var target = $(this).attr('href') + "_inner";
                $( target ).html("<p>Loading...</p>");

                var video_id = $( $(this).attr('href') ).attr("video_id");
                var submission_data = {
                                        video_id : video_id,
                                        video_file : $( $(this).attr('href') ).attr("video_file"),
                                        video_converted : $( $(this).attr('href') ).attr('video_converted')
                                      };

//                console.log("target is: '" + target + "'");

                $.ajax({
                    type: 'POST',
                    url: './get_video.php',
                    data : submission_data,
                    dataType : 'html',
                    success : function(response) {
                        $(target).empty();
                        $(target).append($(response));

                        initialize_event_list();
                        enable_observation_table();
                        initialize_speed_buttons();

                        enable_user_review();
                        enable_expert_review();


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


            }

            if ($( $(this).attr('href') ).hasClass('in')) {
                $( $(this).attr('href') ).removeClass('in');
            } else {
                $( $(this).attr('href') ).addClass('in');
            }

            //$( (this).attr('href') ).collapse('toggle');
            console.log("toggle: " + $( $(this).attr('href') ).hasClass('in'));

            var video_id = $( $(this).attr('href') ).attr("video_id");

            /**
             *  For some reason I need this for snow leopard's safari
             */
//            if ( $("#wildlife-video-span-" + video_id).is(":hidden") ) {
            if ( !$( $(this).attr('href') ).hasClass('in') ) {
                console.log("hiding: wildlife-video-span-" + video_id);

                $("#wildlife-video-span-" + video_id).hide();
                $("#wildlife-video-" + video_id).hide();
//                $("#wildlife-video-buttons-" + video_id).hide();
            } else {
                console.log("showing: wildlife-video-span-" + video_id);

                $("#wildlife-video-span-" + video_id).show();
                $("#wildlife-video-" + video_id).show();
//                $("#wildlife-video-buttons-" + video_id).show();
            }

            ev.preventDefault();
            ev.stopPropagation();
        });
    }

    function init_dropdown() {
        $('#hide-show-sidebar-button').click(function() {
            console.log("toggling sidebar");
            if ($(this).text() == "Hide sidebar") {
                $(this).text("Show sidebar");
                $("#filter-sidebar").hide();
                $("#video-list-body").removeClass("span10");
                $("#video-list-body").addClass("span12");
            } else {
                $(this).text("Hide sidebar");
                $("#filter-sidebar").show();
                $("#video-list-body").removeClass("span12");
                $("#video-list-body").addClass("span10");
            }
        });

        $('.video-nav-list').click(function(ev) {
                var new_min = $(this).attr("id");

                new_min = new_min.substring(11);

                if (video_min != new_min) {
                    video_min = new_min;
                    load_videos(false);
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
                    load_videos();
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
                        load_videos();
                    }
                }
            }
        });


        $('.display-dropdown:not(.bound)').addClass('bound').click(function() {
            var new_count = $(this).attr('count');
            if (video_count != new_count) {
                video_count = new_count;
                load_videos();
            }

            ev.preventDefault();
            ev.stopPropagation();
        });

        $('.video-filter-dropdown:not(.bound)').addClass('bound').click(function() {
            if ($('#video-filter-list').html().indexOf($(this).text()) < 0) {
                $('#display-videos-text').text("Displaying Videos");
                var append_text = "<div class='display-video-table-row' style='display:table-row;'><div style='display:table-cell;'>";
                
                if ($('#video-filter-list').html().length > 0) {
                    append_text += "<span class='label and-label-toggle' style='margin-top:3px; padding-bottom:2px; margin-right:3px;'>and</span>";
                }

                var attr_text = "";
                var desc_text = $(this).text();
                if ($(this).hasClass('location-filter')) {
                    append_text += "<span class='label with-label-toggle' style='margin-top:3px; padding-bottom:2px;'>from</span>";
                    attr_text = "location-filter='" + $(this).attr("location_id") + "'";

                } else if ($(this).hasClass('year-filter')) {
                    append_text += "<span class='label with-label-toggle' style='margin-top:3px; padding-bottom:2px;'>from</span>";
                    attr_text = "year-filter='" + $(this).attr("year") + "'";

                } else if ($(this).hasClass('animal-id-filter')) {
                    append_text += "<span class='label with-label-toggle' style='margin-top:3px; padding-bottom:2px;'>with</span>";
                    desc_text = "id #" + desc_text;
                    attr_text = "animal-id-filter='" + $(this).attr("animal_id") + "'";

                } else if ($(this).hasClass('species-filter')) {
                    append_text += "<span class='label with-label-toggle' style='margin-top:3px; padding-bottom:2px;'>with</span>";
                    attr_text = "species-filter='" + $(this).attr("species_id") + "'";

                } else if ($(this).hasClass('other-video-filter')) {
                    append_text += "<span class='label with-label-toggle' style='margin-top:3px; padding-bottom:2px;'>with</span>";
                    attr_text = "other-video-filter='" + $(this).attr("other_id") + "'";

                } else {    //should not get here
                    console.log("unknown video filter added.");
                }

                append_text += "<br><span class='badge badge-info label-removal-element video-label' style='margin-top:3px; padding-bottom:2px; float:right;' " + attr_text + " label_text='" + desc_text + "'>" + desc_text + "</span></div></div>";
                $('#video-filter-list').append(append_text);
                apply_label_toggles();
                apply_label_removal();
            } else {
                $('#display-videos-text').text("Displaying All Videos");
            }
        });


        $('.event-filter-dropdown:not(.bound)').addClass('bound').click(function() {
            if ($('#event-filter-list').html().indexOf($(this).text()) < 0) {
                $('#display-events-text').text("With Events");
                var append_text = "<div class='display-video-table-row' style='display:table-row;'><div style='display:table-cell;'>";
                
                if ($('#event-filter-list').html().length > 0) {
                    append_text += "<span class='label and-label-toggle' style='margin-top:3px; padding-bottom:2px; margin-right:3px;'>and</span>";
                }

                var attr_text = "";
                var desc_text = $(this).text();
                if ($(this).hasClass('other-event-filter')) {
                    append_text += "<span class='label with-label-toggle' style='margin-top:3px; padding-bottom:2px;'>with</span>";
                    attr_text = "other-event-filter='" + $(this).attr("other_id") + "'";
                } else {    //event-filter
                    append_text += "<span class='label with-label-toggle' style='margin-top:3px; padding-bottom:2px;'>with</span>";
                    attr_text = "event-filter='" + $(this).attr("event_id") + "'";
                }

                append_text += "<br><span class='badge badge-info label-removal-element event-label' style='margin-top:3px; padding-bottom:2px; float:right;' " + attr_text + " label_text='" + desc_text + "'>" + desc_text + "</span></div></div>";
                $('#event-filter-list').append(append_text);
                apply_label_toggles();
                apply_label_removal();
            } else {
                $('#display-events-text').text("With Any Events");
            }
        });

        $('#apply-filter-button:not(.bound)').addClass('bound').click(function() {
            console.log("applying filter!");

            var video_query_text = "";
            $('#video-filter-list span').each(function() {
//                console.log("span text is: '" + $(this).text() + "'");
                var attr_text = "";
                if ($(this).attr('location-filter') !== undefined) {
                    attr_text += "location " + $(this).attr('location-filter');
                } else if ($(this).attr('animal-id-filter') !== undefined) {
                    attr_text += "animal_id " + $(this).attr('animal-id-filter');
                } else if ($(this).attr('year-filter') !== undefined) {
                    attr_text += "year " + $(this).attr('year-filter');
                } else if ($(this).attr('species-filter') !== undefined) {
                    attr_text += "species " + $(this).attr('species-filter');
                } else if ($(this).attr('other-video-filter') !== undefined) {
                    attr_text += "other " + $(this).attr('other-video-filter');
                } else {
                    attr_text = $(this).text();
                }

                video_query_text += attr_text + "##";
            });
            console.log("video_query text: '" + video_query_text + "'");

            var event_query_text = "";
            $('#event-filter-list span').each(function() {
//                console.log("span text is: '" + $(this).text() + "'");
                var attr_text = "";
                if ($(this).attr('event-filter') !== undefined) {
                    attr_text += "event " + $(this).attr('event-filter');
                } else if ($(this).attr('other-event-filter') !== undefined) {
                    attr_text += "other " + $(this).attr('other-event-filter');
                } else {
                    attr_text = $(this).text();
                }

                event_query_text += attr_text + "##";
            });
            console.log("event_query text: '" + event_query_text + "'");

            console.log("video_id_text: '" + video_id_text + "'");

            if (video_filter_text !== video_query_text || event_filter_text !== event_query_text || video_id_text !== $("#video-id-textarea").val()) {
                console.log("RELOADING!");
                video_filter_text = video_query_text;
                event_filter_text = event_query_text;
                video_id_text = $("#video-id-textarea").val();
                load_videos();
            }

        });

        $('#clear-filter-button:not(.bound)').addClass('bound').click(function() {
            $('#display-videos-text').text("Displaying All Videos");
            $('#video-filter-list').text("");
            $('#display-events-text').text("With Any Events");
            $('#event-filter-list').text("");
//            filter_text = "";
//            load_videos();
        });

        $('#all-videos-button:not(.bound)').addClass('bound').click(function() {
            if ($('#all-videos-button').text() === "Showing My Videos") {
                $('#all-videos-button').text("Showing All Videos");
            } else {
                $('#all-videos-button').text("Showing My Videos");
            }
            $(this).toggleClass('active');
            showing_all_videos = !showing_all_videos;
            load_videos();
        });
    }

    function apply_label_toggles() {
        $('.and-label-toggle:not(.bound)').addClass('bound').click(function() {
            var text = $(this).text();
            if (text == 'and') {
                $(this).text('or');
            } else {
                $(this).text('and');
            }
        });

        $('.with-label-toggle:not(.bound)').addClass('bound').click(function() {
            var text = $(this).text();
            if (text == 'from')             $(this).text('not from');
            else if (text == 'not from')    $(this).text('from');
            else if (text == 'with')        $(this).text('without');
            else                            $(this).text('with');
        });
    }

    function apply_label_removal() {
        $('.label-removal-element:not(.over-bound)').addClass('over-bound').mouseover(function(ev) {
            var label_text = $(this).attr("label_text");
//                console.log("mouse is over: '" + label_text + "'");
            if ( !$(this).hasClass("has-remove-icon") ) { 
                $(this).addClass("has-remove-icon");
                $(this).append(" <span class='glyphicon glyphicon-remove-sign'></span>");
            }   
        }); 

        $('.label-removal-element:not(.leave-bound)').addClass('leave-bound').mouseleave(function(ev) {
            var label_text = $(this).attr("label_text");
//                console.log("mouse is over: '" + label_text + "'");
            $(this).text(label_text);
            $(this).removeClass("has-remove-icon");
        }); 

        $('.label-removal-element:not(.click-bound)').addClass('click-bound').click(function(ev) {
            var filter_type_text = "video-";
            if ($(this).hasClass("event-label")) {
                filter_type_text = "event-";
            }

            var label_text = $(this).attr("label_text");

            $(this).parent().parent().remove();

            //check to see if we removed the first filter, if so we need to remove the "and" or "or" before the new first one.
            if ($("#" + filter_type_text + "filter-list").children().length) {
//                console.log("#" + filter_type_text + "filter-list.children().first().html(): " + $("#" + filter_type_text + "filter-list").children().first().html());

                var first_html = $("#" + filter_type_text + "filter-list").children().first().html();
                if (first_html.indexOf(">and</span>") > 0 || first_html.indexOf(">or</span>") > 0) {
                    console.log("trying to remove: " + $("span:first", $("#" + filter_type_text + "filter-list").children().first()).html());

                    $("span:first", $("#" + filter_type_text + "filter-list").children().first()).remove();
                }
            } else {
                if (filter_type_text === "video-") {
                    $('#display-videos-text').text("Displaying All Videos");
                } else {
                    $('#display-events-text').text("With Any Events");
                }
            }
        }); 
    }   

});

