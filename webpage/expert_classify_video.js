$(document).ready(function () {

    var location_id = -1;
    var species_id = -1;
    var animal_id = -1;
    var video_min = 0;
    var video_count = 10;
    var event_ids = {};
    var video_observations = {};

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
                                video_min : video_min,
                                video_count : video_count
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
                            if (response['expert_finished'] === '1') {
                                video_button.removeClass("btn-primary");
                                video_button.addClass("btn-success");
                            } else {
                                video_button.removeClass("btn-success");
                                if (video_observations[video_id] !== null && video_observations[video_id] > 0) {
                                    video_button.addClass("btn-primary");
                                }
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
                        });

                        $('.event-dropdown').click(function(ev) {
                            var event_id = $(this).attr("event_id");
                            var video_id = $(this).attr("video_id");

                            event_ids[video_id] = event_id;

//                            console.log("target = #event-button-" + video_id);
//                            console.log("event_ids = " + JSON.stringify(event_ids));

                            if (event_id === '0') {
                                $('#event-button-' + video_id).html('Event <span class="caret"></span>');
                            } else if (event_id === '1') {
                                $('#event-button-' + video_id).html('Bird Leave <span class="caret"></span>');
                            } else if (event_id === '2') {
                                $('#event-button-' + video_id).html('Bird Return <span class="caret"></span>');
                            } else if (event_id === '3') {
                                $('#event-button-' + video_id).html('Predator <span class="caret"></span>');
                            } else if (event_id === '4') {
                                $('#event-button-' + video_id).html('Other Animal <span class="caret"></span>');
                            } else if (event_id === '5') {
                                $('#event-button-' + video_id).html('Nest Defense <span class="caret"></span>');
                            } else if (event_id === '6') {
                                $('#event-button-' + video_id).html('Nest Success <span class="caret"></span>');
                            }

                            ev.preventDefault();
//                            ev.stopPropagation();
                        });

                    },
                    error : function(jqXHR, textStatus, errorThrown) {
                        alert(errorThrown);
                    },
                    async: true
                });
            }

            $( $(this).attr('href') ).collapse('toggle');
    //            console.log("toggle: " + $( $(this).attr('href') ).toggled);

            ev.preventDefault();
            ev.stopPropagation();
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

