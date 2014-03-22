$(document).ready(function () {

    /*
    var path = document.location.pathname;
    var dev_dir = path.substr(path.indexOf('/', 1) + 1, path.lastIndexOf('/') - path.indexOf('/', 1));

    console.log("path:    '" + path + "'");
    console.log("dev_dir: '" + dev_dir + "'");
    */

    var filter_text = '';
    var location_id = -1;
    var species_id = -1;
    var animal_id = -1;
    var video_min = 0;
    var video_count = 15;
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
            url: './expert_interface/get_animal_ids.php',
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
                                filter_text : filter_text,
                                video_min : video_min,
                                video_count : video_count,
                              };

        $.ajax({
            type: 'POST',
            url: './expert_interface/get_expert_videos.php',
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

                    $.ajax({
                        type: 'POST',
                        url: './expert_interface/toggle_expert_flag.php',
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
            url: './get_video_count_nav.php',
            data : submission_data,
            dataType : 'text',
            success : function(response) {
                console.log("the response was:\n" + response);
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
                    url: './expert_interface/get_expert_video.php',
                    data : submission_data,
                    dataType : 'text',
                    success : function(response) {
                        console.log("the response was:\n" + response);
                        $(target).html(response);

                        initialize_event_list();
                        enable_observation_table();
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

        $('.filter-dropdown:not(.bound)').addClass('bound').click(function() {
            if ($('#filter-list').html().indexOf($(this).text()) < 0) {
                $('#display-videos-text').text("Displaying Videos");
                var append_text = "<div style='display:table-row;'><div style='display:table-cell;'>";
                
                if ($('#filter-list').html().length > 0) {
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

                } else if ($(this).hasClass('other-filter')) {
                    append_text += "<span class='label with-label-toggle' style='margin-top:3px; padding-bottom:2px;'>with</span>";
                    attr_text = "other-filter";

                } else {    //event-filter
                    append_text += "<span class='label with-label-toggle' style='margin-top:3px; padding-bottom:2px;'>with</span>";
                    attr_text = "event-filter='" + $(this).attr("event_id") + "'";
                }

                append_text += "<span class='badge badge-info' style='margin-top:3px; padding-bottom:2px; float:right;' " + attr_text + ">" + desc_text + "</span></div></div>";
                $('#filter-list').append(append_text);
                apply_label_toggles();
            } else {
                $('#display-videos-text').text("Displaying All Videos");
            }
        });

        $('#apply-filter-button:not(.bound)').addClass('bound').click(function() {
            console.log("applying filter!");

            var query_text = "";
            $('#filter-list span').each(function() {
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
                } else if ($(this).attr('event-filter') !== undefined) {
                    attr_text += "event " + $(this).attr('event-filter') + " ";
                } else if ($(this).attr('other-filter') !== undefined) {
                    attr_text += "other ";
                } else {
                    attr_text = $(this).text();
                }

                query_text += attr_text + "##";
            });
//            console.log("query text: '" + query_text + "'");

            if (filter_text !== query_text) {
                console.log("RELOADING!");
                filter_text = query_text;
                load_videos();
            }

        });

        $('#clear-filter-button:not(.bound)').addClass('bound').click(function() {
            console.log("clearing filter!");

            $('#display-videos-text').text("Displaying All Videos");
            $('#filter-list').text("");
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
});

