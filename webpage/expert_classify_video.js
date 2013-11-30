$(document).ready(function () {

    var location_id = -1;
    var species_id = -1;
    var animal_id = -1;
    var video_min = 0;
    var video_count = 10;
    var event_ids = {};
    var comments = {};
    var event_end_times = {};
    var event_start_times = {};
    var video_observations = {};
    var year = '';
    var video_status = '';
    var video_release = '';

    $('.status-dropdown').click(function() {
        var new_status = $(this).attr("video_status");

        if (new_status !== video_status) {
            video_status = new_status;
            if (video_status === '') {
                $('#status-button').html("Any Status <span class='caret'></span>");
            } else if (video_status === 'UNWATCHED') {
                $('#status-button').html("Unwatched <span class='caret'></span>");
            } else if (video_status === 'WATCHED') {
                $('#status-button').html("Watched <span class='caret'></span>");
            } else if (video_status === 'FINISHED') {
                $('#status-button').html("Finished <span class='caret'></span>");
            }

            load_animal_ids();
            load_videos();
        }
    });

    $('.release-dropdown').click(function() {
        var new_release = $(this).attr("release_to_public");

        if (new_release !== video_release) {
            video_release = new_release;
            if (video_release === '') {
                $('#release-button').html("Release <span class='caret'></span>");
            } else if (video_release === 'false') {
                $('#release-button').html("Private <span class='caret'></span>");
            } else if (video_release === 'true') {
                $('#release-button').html("Public <span class='caret'></span>");
            }

            load_animal_ids();
            load_videos();
        }
    });


    $('.year-dropdown').click(function() {
        var new_year = $(this).attr("year");

        if (new_year !== year) {
            year = new_year;
            if (year === '') {
                $('#year-button').html("Year <span class='caret'></span>");
            } else {
                $('#year-button').html(year + " <span class='caret'></span>");
            }

            load_animal_ids();
            load_videos();
        }
    });

    $('.species-dropdown').click(function() {
        var new_species_id = $(this).attr("species_id");

        if (new_species_id !== species_id) {
            species_id = new_species_id;

            if (species_id == 0) {
                $('#species-button').html('Species <span class="caret"></span>');
            } else if (species_id == 1) {
                $('#species-button').html('Sharp-tailed Grouse <span class="caret"></span>');
            } else if (species_id == 2) {
                $('#species-button').html('Interior Least Tern <span class="caret"></span>');
            } else if (species_id == 3) {
                $('#species-button').html('Piping Plover <span class="caret"></span>');
            }

            load_animal_ids();
            load_videos();
        }
    });

    $('.location-dropdown').click(function() {
        var new_location_id = $(this).attr("location_id");

        if (new_location_id !== location_id) {
            location_id = new_location_id;

            if (location_id == 0) {
                $('#location-button').html('Location <span class="caret"></span>');
            } else if (location_id == 1) {
                $('#location-button').html('Belden, ND <span class="caret"></span>');
            } else if (location_id == 2) {
                $('#location-button').html('Blaisdell, ND <span class="caret"></span>');
            } else if (location_id == 3) {
                $('#location-button').html('Lostwood Wildlife Refuge, ND <span class="caret"></span>');
            } else if (location_id == 4) {
                $('#location-button').html('Missouri River, ND <span class="caret"></span>');
            }

            load_animal_ids();
            load_videos();
        }
    });

    load_animal_ids();

    function load_animal_ids() {
        animal_id = -1
        $('#animal-id-button').html("Animal ID <span class='caret'></span>");

        var submission_data = {
                                species_id : species_id,
                                location_id : location_id,
                                video_status : video_status,
                                year : year
                              };

        $.ajax({
            type: 'POST',
            url: './get_animal_ids.php',
            data : submission_data,
            dataType : 'text',
            success : function(response) {
//                console.log("the response was:\n" + response);
                $("#animal-id-dropdown-menu").html(response);
                enable_animal_id_dropdown();
            },
            error : function(jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            },
            async: true
        });
    }

    function enable_animal_id_dropdown() {
        $('.animal-id-dropdown').click(function() {
            var new_animal_id = $(this).attr("animal_id");

            if (new_animal_id !== animal_id) {
                animal_id = new_animal_id;

                if (animal_id == 0) {
                    $('#animal-id-button').html('Animal ID <span class="caret"></span>');
                } else {
                    $('#animal-id-button').html(animal_id + ' <span class="caret"></span>');
                }
                load_videos();
            }
        });
    }

    load_videos();

    function load_videos() {
        var submission_data = {
                                species_id : species_id,
                                location_id : location_id,
                                animal_id : animal_id,
                                year : year,
                                video_min : video_min,
                                video_count : video_count,
                                video_status : video_status,
                                video_release : video_release
                              };

        $.ajax({
            type: 'POST',
            url: './get_expert_videos.php',
            data : submission_data,
            dataType : 'text',
            success : function(response) {
//                console.log("the response was:\n" + response);
                $("#video-list-placeholder").html(response);
                enable_accordion();

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
                        url: './toggle_private_video.php',
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

                    $.ajax({
                        type: 'POST',
                        url: './toggle_expert_flag.php',
                        data : { video_id : video_id },
                        dataType : 'JSON',
                        success : function(response) {
//                            console.log("response: " + JSON.stringify(response));
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
            url: './get_expert_count_nav.php',
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


    function enable_accordion() {
        $('.accordion-toggle').click(function(ev) {
            console.log("clicked an accordion toggle with href: " + $(this).attr('href'));

            if ($( $(this).attr('href') + "_inner" ).html().indexOf('uninitialized') != -1) {
                var target = $(this).attr('href') + "_inner";
                $( target ).html("<p>Loading...</p>");

                var submission_data = {
                                        video_id : $( $(this).attr('href') ).attr("video_id"),
                                        video_file : $( $(this).attr('href') ).attr("video_file"),
                                        video_converted : $( $(this).attr('href') ).attr('video_converted')
                                      };

                console.log("target is: '" + target + "'");

                $.ajax({
                    type: 'POST',
                    url: './get_expert_video.php',
                    data : submission_data,
                    dataType : 'text',
                    success : function(response) {
        //                console.log("the response was:\n" + response);
                        $(target).html(response);

                        $('.event-start-time-textbox').click(function() {
                            var video_id = $(this).attr("video_id");
//                            console.log("setting text for #wildlife-video-" + video_id);

                            var video_start_time = $("#collapse_" + video_id).attr("start_time");
                            var time = $("#wildlife-video-" + video_id).get(0).currentTime * 1000;

                            //convert the mysql datetime to a javascript Date object
                            var t = video_start_time.split(/[- :]/);
                            var video_date = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
                            console.log("video_date: " + video_date);
                            console.log("time: " + time);

                            var current_time = new Date(video_date.getTime() + time);

                            var hours = current_time.getHours();
                            var minutes = current_time.getMinutes();
                            var seconds = current_time.getSeconds();
                            var result = (hours < 10 ? "0" + hours : hours) + ":" + (minutes < 10 ? "0" + minutes : minutes) + ":" + (seconds  < 10 ? "0" + seconds : seconds);

//                            console.log( "Time: " + result);
                            $(this).val( result );
                            event_start_times[video_id] = result;
                        });

                        $('.event-end-time-textbox').click(function() {
                            var video_id = $(this).attr("video_id");
//                            console.log("setting text for #wildlife-video-" + video_id);

                            var video_start_time = $("#collapse_" + video_id).attr("start_time");
                            var time = $("#wildlife-video-" + video_id).get(0).currentTime * 1000;

                            //convert the mysql datetime to a javascript Date object
                            var t = video_start_time.split(/[- :]/);
                            var video_date = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
//                            console.log("video_date: " + video_date);

                            var current_time = new Date(video_date.getTime() + time);

                            var hours = current_time.getHours();
                            var minutes = current_time.getMinutes();
                            var seconds = current_time.getSeconds();
                            var result = (hours < 10 ? "0" + hours : hours) + ":" + (minutes < 10 ? "0" + minutes : minutes) + ":" + (seconds  < 10 ? "0" + seconds : seconds);

//                            console.log( "Time: " + result);
                            $(this).val( result );
                            event_end_times[video_id] = result;
                        });

                        $('.event-dropdown').click(function(ev) {
                            var event_id = $(this).attr("event_id");
                            var video_id = $(this).attr("video_id");

                            event_ids[video_id] = event_id;

//                            console.log("target = #event-button-" + video_id);
//                            console.log("event_ids = " + JSON.stringify(event_ids));

                            function toTitleCase(str) {
                                return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
                            }

                            $('#event-button-' + video_id).html(toTitleCase(event_ids[video_id]) + ' <span class="caret"></span>');

                            /*
                            if (event_id === '0') {
                                $('#event-button-' + video_id).html('Unspecified <span class="caret"></span>');
                                event_ids[video_id] = 'unspecified';
                            } else if (event_id === '1') {
                                $('#event-button-' + video_id).html('Bird Presence <span class="caret"></span>');
                                event_ids[video_id] = 'bird present';
                            } else if (event_id === '2') {
                                $('#event-button-' + video_id).html('Bird Absence <span class="caret"></span>');
                                event_ids[video_id] = 'bird absent';
                            } else if (event_id === '3') {
                                $('#event-button-' + video_id).html('Predator <span class="caret"></span>');
                                event_ids[video_id] = 'territorial - predator';
                            } else if (event_id === '4') {
                                $('#event-button-' + video_id).html('Other Animal <span class="caret"></span>');
                                event_ids[video_id] = 'territorial - other animal';
                            } else if (event_id === '5') {
                                $('#event-button-' + video_id).html('Nest Defense <span class="caret"></span>');
                                event_ids[video_id] = 'territorial - nest defense';
                            } else if (event_id === '6') {
                                $('#event-button-' + video_id).html('Nest Success <span class="caret"></span>');
                                event_ids[video_id] = 'nest success';
                            } else if (event_id === '7') {
                                $('#event-button-' + video_id).html('Chick Presence <span class="caret"></span>');
                                event_ids[video_id] = 'chick presence';
                            } else if (event_id === '8') {
                                $('#event-button-' + video_id).html('Volunteer Training<span class="caret"></span>');
                                event_ids[video_id] = 'volunteer training';
                            }
                            */

                            ev.preventDefault();
//                            ev.stopPropagation();
                        });

                        $('.fast-forward-button').button();
                        $('.fast-backward-button').button();

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


                        $('.submit-observation-button').click(function() {
                            var video_id = $(this).attr("video_id");

                            $(this).addClass("disabled");
                            var div_id = "#submit-observation-button-" + video_id;

                            var submission_data = {
                                                    video_id : video_id,
                                                    user_id : user_id,
                                                    event_type : event_ids[video_id],
                                                    start_time : $("#event-start-time-" + video_id).val(),
                                                    end_time : $("#event-end-time-" + video_id).val(),
                                                    comments : $("#comments-" + video_id).val()
                                                  };

                            $.ajax({
                                type: 'POST',
                                url: './submit_expert_observation.php',
                                data : submission_data,
                                dataType : 'json',
                                success : function(response) {
                                    //console.log("the response was:\n" + response);
                                    $(div_id).removeClass("disabled");

                                    var observation_id = response['observation_id'];
                                    $("#observations-table-div-" + video_id).html( response['html'] );

                                    var recorded_event_button_html = response['observation_count'];
                                    if (recorded_event_button_html === 1) recorded_event_button_html += " recorded event";
                                    else recorded_event_button_html += " recorded events";
                                    $("#recorded-event-button-" + video_id).html( recorded_event_button_html );

                                    enable_remove_observation_buttons();
                                },
                                error : function(jqXHR, textStatus, errorThrown) {
                                    alert(errorThrown);
                                },
                                async: true
                            });
                        });

                        enable_remove_observation_buttons();
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

    function enable_remove_observation_buttons() {
        $('.remove-observation-button').button();
        $('.remove-observation-button').click(function() {
            var observation_id = $(this).attr('observation_id');
            var div_id = "#remove-observation-button-" + observation_id;
            console.log("observation id: " + observation_id);
            $(this).addClass("disabled");

            var submission_data = {
                                    observation_id : observation_id
                                  };
            $.ajax({
                type: 'POST',
                url: './remove_expert_observation.php',
                data : submission_data,
                dataType : 'json',
                success : function(response) {
                    $(div_id).removeClass("disabled");

                    var observation_count = response['observation_count'];
                    var video_id = $("#observation-row-" + observation_id).parent().parent().attr('video_id');
                    $("#observations-table-div-" + video_id).html( response['html'] );

                    var recorded_event_button_html = response['observation_count'];
                    if (recorded_event_button_html === 1) recorded_event_button_html += " recorded event";
                    else recorded_event_button_html += " recorded events";
                    $("#recorded-event-button-" + video_id).html( recorded_event_button_html );

                    enable_remove_observation_buttons();
                },
                error : function(jqXHR, textStatus, errorThrown) {
                    alert(errorThrown);
                },
                async: true
            });
        });
    }


    function init_dropdown() {
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


        $('#display-5-dropdown').click(function(ev) {
            if (video_count != 5) {
                video_count = 5;
                load_videos();
            }

            ev.preventDefault();
            ev.stopPropagation();
        });

        $('#display-10-dropdown').click(function(ev) {
            if (video_count != 10) {
                video_count = 10;
                load_videos();
            }

            ev.preventDefault();
            ev.stopPropagation();
        });

        $('#display-20-dropdown').click(function(ev) {
            if (video_count != 20) {
                video_count = 20;
                load_videos();
            }

            ev.preventDefault();
            ev.stopPropagation();
        });
    }


});

