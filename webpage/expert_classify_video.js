$(document).ready(function () {

    var location_id = -1;
    var species_id = -1;
    var animal_id = -1;
    var video_min = 0;
    var video_count = 10;
    var event_ids = {};
    var comments = {};
    var event_times = {};
    var video_observations = {};
    var year = '';
    var video_status = '';

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
                                location_id : location_id
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
                                video_status : video_status
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
//            console.log("clicked an accordion toggle with href: " + $(this).attr('href'));

            if ($( $(this).attr('href') + "_inner" ).html().indexOf('uninitialized') != -1) {
                var target = $(this).attr('href') + "_inner";
                $( target ).html("<p>Loading...</p>");

                var submission_data = {
                                        video_id : $( $(this).attr('href') ).attr("video_id"),
                                        video_file : $( $(this).attr('href') ).attr("video_file")
                                      };

                $.ajax({
                    type: 'POST',
                    url: './get_expert_video.php',
                    data : submission_data,
                    dataType : 'text',
                    success : function(response) {
        //                console.log("the response was:\n" + response);
                        $(target).html(response);

                        $('.event-time-textbox').click(function() {
                            var video_id = $(this).attr("video_id");
//                            console.log("setting text for #wildlife-video-" + video_id);

                            var time = Math.floor( $("#wildlife-video-" + video_id).get(0).currentTime );

                            var hours = parseInt( time / 3600 ) % 24;
                            var minutes = parseInt( time / 60 ) % 60;
                            var seconds = time % 60;

                            var result = (hours < 10 ? "0" + hours : hours) + ":" + (minutes < 10 ? "0" + minutes : minutes) + ":" + (seconds  < 10 ? "0" + seconds : seconds);

//                            console.log( "Time: " + result);
                            $(this).val( result );
                            event_times[video_id] = result;
                        });

                        $('.event-dropdown').click(function(ev) {
                            var event_id = $(this).attr("event_id");
                            var video_id = $(this).attr("video_id");

                            event_ids[video_id] = event_id;

//                            console.log("target = #event-button-" + video_id);
//                            console.log("event_ids = " + JSON.stringify(event_ids));

                            if (event_id === '0') {
                                $('#event-button-' + video_id).html('Unspecified <span class="caret"></span>');
                                event_ids[video_id] = 'UNSPECIFIED';
                            } else if (event_id === '1') {
                                $('#event-button-' + video_id).html('Bird Leave <span class="caret"></span>');
                                event_ids[video_id] = 'BIRD_LEAVE';
                            } else if (event_id === '2') {
                                $('#event-button-' + video_id).html('Bird Return <span class="caret"></span>');
                                event_ids[video_id] = 'BIRD_RETURN';
                            } else if (event_id === '3') {
                                $('#event-button-' + video_id).html('Predator <span class="caret"></span>');
                                event_ids[video_id] = 'PREDATOR';
                            } else if (event_id === '4') {
                                $('#event-button-' + video_id).html('Other Animal <span class="caret"></span>');
                                event_ids[video_id] = 'OTHER_ANIMAL';
                            } else if (event_id === '5') {
                                $('#event-button-' + video_id).html('Nest Defense <span class="caret"></span>');
                                event_ids[video_id] = 'NEST_DEFENSE';
                            } else if (event_id === '6') {
                                $('#event-button-' + video_id).html('Nest Success <span class="caret"></span>');
                                event_ids[video_id] = 'NEST_SUCCESS';
                            }

                            ev.preventDefault();
//                            ev.stopPropagation();
                        });

                        $('.fast-forward-button').button();
                        $('.fast-backward-button').button();

                        $('.fast-backward-button').click(function() {
                            var video_id = $(this).attr('video_id');
                            var video = $('#wildlife-video-' + video_id).get(0);
                            var rate = video.playbackRate;

                            rate -= 2.0;
                            if (rate < -9.0) rate = -9.0;

                            video.playbackRate = rate;

                            //console.log("clicking fast backward!, playback rate: " + video.playbackRate);

                            $('#speed-textbox-' + video_id).val("speed:" + video.playbackRate);
                        });

                        $('.fast-forward-button').click(function() {
                            var video_id = $(this).attr('video_id');
                            var video = $('#wildlife-video-' + video_id).get(0);
                            var rate = video.playbackRate;

                            rate += 2.0;
                            if (rate > 9.0) rate = 9.0;

                            video.playbackRate = rate;

                            //console.log("clicking fast forward!, playback rate: " + video.playbackRate);

                            $('#speed-textbox-' + video_id).val("speed:" + video.playbackRate);
                        });


                        $('.submit-observation-button').click(function() {
                            var video_id = $(this).attr("video_id");

                            var submission_data = {
                                                    video_id : video_id,
                                                    user_id : user_id,
                                                    event_type : event_ids[video_id],
                                                    event_time : event_times[video_id],
                                                    comments : $("#comments-" + video_id).val()
                                                  };

                            $.ajax({
                                type: 'POST',
                                url: './submit_expert_observation.php',
                                data : submission_data,
                                dataType : 'json',
                                success : function(response) {
                                    //console.log("the response was:\n" + response);

                                    var observation_id = response['observation_id'];

                                    if ($("#observations-table-div-" + video_id).html() == '') {
                                        var text = "<table class='table table-striped table-bordered table-condensed observations-table' video_id='" + video_id + "' id='observations-table-" + video_id + "'>";
                                        text += "<thead><th>User</th><th>Event</th><th>Time</th><th>Comments</th></thead>";
                                        text += "<tbody>";
                                        text += "<tr observation_id='" + observation_id + "' id='observation-row-" + observation_id + "'> <td>" + user_name + "</td> <td>" + event_ids[video_id] + "</td> <td>" + event_times[video_id] + "</td> <td>" + $("#comments-" + video_id).val() + "</td> <td style='padding-top:0px; padding-bottom:0px; width:25px;'> <button class='btn btn-small btn-danger pull-right remove-observation-button' id='remove-observation-button-" + observation_id + "' observation_id='" + observation_id + "' style='margin-top:3px; margin-bottom:0px; padding-top:0px; padding-bottom:0px;'> - </button> </td> </tr>";
                                        text += "</tbody>";
                                        text += "</table>";

                                        $("#observations-table-div-" + video_id).html( text );

                                        video_observations[video_id] = 1;
                                        if ( ! $("#tag-video-button-" + video_id).hasClass("btn-success") ) {
                                            $("#tag-video-button-" + video_id).addClass("btn-primary");
                                        }
                                    } else {
                                        var text = "<tr observation_id='" + observation_id + "' id='observation-row-" + observation_id + "'> <td>" + user_name + "</td> <td>" + event_ids[video_id] + "</td> <td>" + event_times[video_id] + "</td> <td>" + $("#comments-" + video_id).val() + "</td> <td style='padding-top:0px; padding-bottom:0px; width:25px;'> <button class='btn btn-small btn-danger pull-right remove-observation-button' id='remove-observation-button-" + observation_id + "' observation_id='" + observation_id + "' style='margin-top:3px; margin-bottom:0px; padding-top:0px; padding-bottom:0px;'> - </button> </td> </tr>";

                                        $("#observations-table-div-" + video_id + " tr:last").after( text );

                                        video_observations[video_id] = 2;
                                        if ( ! $("#tag-video-button-" + video_id).hasClass("btn-success") ) {
                                            $("#tag-video-button-" + video_id).addClass("btn-primary");
                                        }
                                    }

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

            $( $(this).attr('href') ).collapse('toggle');
    //            console.log("toggle: " + $( $(this).attr('href') ).toggled);

            var video_id = $( $(this).attr('href') ).attr("video_id");

            /**
             *  For some reason I need this for snow leopard's safari
             */
            if ($("#wildlife-video-" + video_id).is(":hidden")) {
                $("#wildlife-video-" + video_id).show();
            } else {
                $("#wildlife-video-" + video_id).hide();
            }

            ev.preventDefault();
            ev.stopPropagation();
        });
    }

    function enable_remove_observation_buttons() {
        $('.remove-observation-button').button();
        $('.remove-observation-button').click(function() {
            var observation_id = $(this).attr('observation_id');
            console.log("observation id: " + observation_id);

            var submission_data = {
                                    observation_id : observation_id
                                  };
            $.ajax({
                type: 'POST',
                url: './remove_expert_observation.php',
                data : submission_data,
                dataType : 'json',
                success : function(response) {
                    var observation_count = response['observation_count'];

                    $("#observation-row-" + observation_id).hide();

                    if (observation_count === '0') {
//                        console.log("hiding parent: " + $("#observation-row-" +observation_id).parent().parent().parent().html());
                        var video_id = $("#observation-row-" + observation_id).parent().parent().attr('video_id');

                        $("#observation-row-" + observation_id).parent().parent().parent().html("");

                        /**
                         * Need to update the tag button too
                         */
                        if ($("#tag-video-button-" + video_id).hasClass("btn-primary")) $("#tag-video-button-" + video_id).removeClass("btn-primary");
                    }
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

