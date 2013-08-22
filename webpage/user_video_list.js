
$(document).ready(function () {

    $('#display-any-location-dropdown').click(function() {
        if (location_id != -1) {
            location_id = -1;
            
            $('#location-button').html('Any Location <span class="caret"></span>');

            if (filter != '') {
                reload_videos();
            }
        }
    });

    $('#display-belden-dropdown').click(function() {
        if (location_id != 1) {
            location_id = 1;
            
            $('#location-button').html('Belden, ND <span class="caret"></span>');

            if (filter != '') {
                reload_videos();
            }
        }
    });

    $('#display-blaisdell-dropdown').click(function() {
        if (location_id != 2) {
            location_id = 2;
            
            $('#location-button').html('Blaisdell, ND <span class="caret"></span>');

            if (filter != '') {
                reload_videos();
            }
        }
    });

    $('#display-lostwood-dropdown').click(function() {
        if (location_id != 3) {
            location_id = 3;
            
            $('#location-button').html('Lostwood Wildlife Refuge, ND <span class="caret"></span>');

            if (filter != '') {
                reload_videos();
            }
        }
    });

    $('#display-missouri-river-dropdown').click(function() {
        if (location_id != 4) {
            location_id = 4;
            
            $('#location-button').html('Missouri River, ND <span class="caret"></span>');

            if (filter != '') {
                reload_videos();
            }
        }
    });

    $('#display-any-species-dropdown').click(function() {
        if (species_id != -1) {
            species_id = -1;
            
            $('#species-button').html('Any Species <span class="caret"></span>');

            if (filter != '') {
                reload_videos();
            }
        }
    });


    $('#display-grouse-dropdown').click(function() {
        if (species_id != 1) {
            species_id = 1;
            
            $('#species-button').html('Sharp-tailed Grouse <span class="caret"></span>');

            if (filter != '') {
                reload_videos();
            }
        }
    });

    $('#display-tern-dropdown').click(function() {
        if (species_id != 2) {
            species_id = 2;
            
            $('#species-button').html('Interior Least Tern <span class="caret"></span>');

            if (filter != '') {
                reload_videos();
            }
        }
    });

    $('#display-plover-dropdown').click(function() {
        if (species_id != 3) {
            species_id = 3;
            
            $('#species-button').html('Piping Plover <span class="caret"></span>');

            if (filter != '') {
                reload_videos();
            }
        }
    });


    var video_min = 0;
    var video_count = 5;
    var filter = '';
    var species_id = -1;
    var location_id = -1;

    var filters =   {
                        interesting : 'no',
                        too_dark : 'no',
                        corrupt : 'no',
                        invalid : 'any',
                        bird_presence : 'any',
                        bird_absence : 'any',
                        chick_presence : 'any',
                        predator_presence : 'any',
                        nest_defense : 'any',
                        nest_success : 'any',
                        bird_leave : 'any',
                        bird_return : 'any'
                    };

    reload_videos();

    function reload_videos(reset_video_min) {
        if (reset_video_min === undefined) video_min = 0;

        var submission_data = {
                                filter : filter,
                                species_id : species_id,
                                location_id : location_id,
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

                $(".report-observations-button").button();
                $(".report-observations-button").click(function() {
                    var video_segment_id = $(this).attr("video_segment_id");
                    console.log("video segment 2 id is: " + video_segment_id);

                    var text = "<p>Please enter a description of why you are reporting this video:</p>" +
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
                                    user_id : user_id,
                                    user_name : user_name,
                                    report_comments : comments
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

    $('.nav-li').click(function() {
        if ($(this).hasClass('label-info')) {
            if ($(this).text().indexOf(' - Unsure') > 0) {
                $(this).removeClass('label-info');
                $(this).text( $(this).text().substr(0, $(this).text().indexOf(' - ')) );
            } else if ($(this).text().indexOf(' - Yes') > 0) {
                $(this).text( $(this).text().substr(0, $(this).text().indexOf(' - ')) );
                $(this).text( $(this).text() + ' - No' );
            } else if ($(this).text().indexOf(' - No') > 0) {
                $(this).text( $(this).text().substr(0, $(this).text().indexOf(' - ')) );
                $(this).text( $(this).text() + ' - Unsure' );

            } else if ($(this).text() == 'Invalid') {
                $(this).text('Unvalidated');
            } else if ($(this).text() == 'Unvalidated') {
                $(this).text('Valid');
            } else if ($(this).text() == 'Valid') {
                $(this).text('Invalid');
                $(this).removeClass('label-info');

            } else {
                /**
                 *  This will be the case for Corrupt and Too Dark
                 */
                $(this).removeClass('label-info');
            }

        } else {
            $(this).addClass('label-info');
            if ($(this).text() != 'Invalid' && $(this).text() != 'Too Dark' && $(this).text() != 'Corrupt' && $(this).text() != 'Interesting') {
                $(this).text( $(this).text() + ' - Yes' );
            }
        }

        filter = $(this).attr("id");

        function update_filters(filter_type, binary_filter) {
            if (filter_type == 'invalid') {
                if (filters[filter_type] === 'yes') filters[filter_type] = 'unsure';
                else if (filters[filter_type] === 'unsure') filters[filter_type] = 'no';
                else if (filters[filter_type] === 'no') filters[filter_type] = 'any';
                else if (filters[filter_type] === 'any') filters[filter_type] = 'yes';

            } else  if (binary_filter === undefined) {
                if (filters[filter_type] === 'yes') filters[filter_type] = 'no';
                else if (filters[filter_type] === 'no') filters[filter_type] = 'unsure';
                else if (filters[filter_type] === 'unsure') filters[filter_type] = 'any';
                else if (filters[filter_type] === 'any') filters[filter_type] = 'yes';
            } else {
                if (filters[filter_type] === 'yes') filters[filter_type] = 'no';
                else if (filters[filter_type] === 'no') filters[filter_type] = 'yes';
            }
        }

//        if (filter === 'interesting-nav-pill')  filters.interesting = !filters.interesting;
        if (filter === 'interesting-nav-pill')  update_filters('interesting', true);
        else if (filter === 'invalid-nav-pill') update_filters('invalid', true);
        else if (filter === 'bird-presence-nav-pill') update_filters('bird_presence');
        else if (filter === 'bird-absence-nav-pill') update_filters('bird_absence');
        else if (filter === 'chick-presence-nav-pill')  update_filters('chick_presence');
        else if (filter === 'predator-presence-nav-pill') update_filters('predator_presence');
        else if (filter === 'nest-defense-nav-pill') update_filters('nest_defense');
        else if (filter === 'nest-success-nav-pill') update_filters('nest_success');
        else if (filter === 'bird-leave-nav-pill') update_filters('bird_leave');
        else if (filter === 'bird-return-nav-pill') update_filters('bird_return');
        else if (filter === 'too-dark-nav-pill') update_filters('too_dark', true);
        else if (filter === 'corrupt-nav-pill') update_filters('corrupt', true);

        reload_videos();
    });

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



