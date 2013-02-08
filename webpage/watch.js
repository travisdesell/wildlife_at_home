
$(document).ready(function () {
    $('#fast_forward_button').button();
    $('#fast_backward_button').button();

    $('#fast_backward_button').click(function() {
        var video = $('#wildlife_video').get(0);
        var rate = video.playbackRate;
        
        rate -= 2.0;
        if (rate < -9.0) rate = -9.0;

        video.playbackRate = rate;

//        console.log("clicking fast backward!, playback rate: " + video.playbackRate);

        $('#speed_textbox').val("speed:" + video.playbackRate);
    });


    $('#fast_forward_button').click(function() {
        var video = $('#wildlife_video').get(0);
        var rate = video.playbackRate;

        rate += 2.0;
        if (rate > 9.0) rate = 9.0;

        video.playbackRate = rate;

//        console.log("clicking fast forward!, playback rate: " + video.playbackRate);

        $('#speed_textbox').val("speed: " + video.playbackRate);
    });


    /**
     * Set controls for the radio buttons, comments and submit button.
     */
    var bird_leave_selected = false;
    var bird_return_selected = false;
    var bird_absence_selected = false;
    var bird_presence_selected = false;
    var predator_presence_selected = false;
    var nest_defense_selected = false;
    var nest_success_selected = false;
    var interesting_selected = false;

    /**
     *  1 : yes
     *  0 : unsure
     * -1 : no
     */
    var bird_leave = 0;
    var bird_return = 0;
    var bird_absence = 0;
    var bird_presence = 0;
    var predator_presence = 0;
    var nest_defense = 0;
    var nest_success = 0;
    var interesting = 0;


    function enable_submit() {
        /*
        console.log("bird_leave_selected: " + bird_leave_selected);
        console.log("bird_return_selected: " + bird_return_selected);
        console.log("bird_absence_selected: " + bird_absence_selected);
        console.log("bird_presence_selected: " + bird_presence_selected);
        console.log("predator_presence_selected: " + predator_presence_selected);
        console.log("nest_success_selected: " + nest_success_selected);
        console.log("nest_defense_selected: " + nest_defense_selected);
        console.log("interesting_selected: " + interesting_selected);
        */

        if (bird_leave_selected && bird_return_selected && bird_absence_selected && bird_presence_selected &&
            predator_presence_selected && nest_defense_selected && nest_success_selected && interesting_selected) {

            $('#submit_button').removeClass("disabled");
        }
    }

    $('#bird_leave_yes').click(function() {
        bird_leave = 1;
        bird_leave_selected = true;
        enable_submit();
    });

    $('#bird_leave_unsure').click(function() {
        bird_leave = 0;
        bird_leave_selected = true;
        enable_submit();
    });

    $('#bird_leave_no').click(function() {
        bird_leave = -1;
        bird_leave_selected = true;
        enable_submit();
    });

    $('#bird_return_yes').click(function() {
        bird_return = 1;
        bird_return_selected = true;
        enable_submit();
    });

    $('#bird_return_unsure').click(function() {
        bird_return = 0;
        bird_return_selected = true;
        enable_submit();
    });

    $('#bird_return_no').click(function() {
        bird_return = -1;
        bird_return_selected = true;
        enable_submit();
    });

    $('#bird_absence_yes').click(function() {
        bird_absence = 1;
        bird_absence_selected = true;
        enable_submit();
    });

    $('#bird_absence_unsure').click(function() {
        bird_absence = 0;
        bird_absence_selected = true;
        enable_submit();
    });

    $('#bird_absence_no').click(function() {
        bird_absence = -1;
        bird_absence_selected = true;
        enable_submit();
    });

    $('#bird_presence_yes').click(function() {
        bird_presence = 1;
        bird_presence_selected = true;
        enable_submit();
    });

    $('#bird_presence_unsure').click(function() {
        bird_presence = 0;
        bird_presence_selected = true;
        enable_submit();
    });

    $('#bird_presence_no').click(function() {
        bird_presence = -1;
        bird_presence_selected = true;
        enable_submit();
    });

    $('#predator_presence_yes').click(function() {
        predator_presence = 1;
        predator_presence_selected = true;
        enable_submit();
    });

    $('#predator_presence_unsure').click(function() {
        predator_presence = 0;
        predator_presence_selected = true;
        enable_submit();
    });

    $('#predator_presence_no').click(function() {
        predator_presence = -1;
        predator_presence_selected = true;
        enable_submit();
    });

    $('#nest_defense_yes').click(function() {
        nest_defense = 1;
        nest_defense_selected = true;
        enable_submit();
    });

    $('#nest_defense_unsure').click(function() {
        nest_defense = 0;
        nest_defense_selected = true;
        enable_submit();
    });

    $('#nest_defense_no').click(function() {
        nest_defense = -1;
        nest_defense_selected = true;
        enable_submit();
    });

    $('#nest_success_yes').click(function() {
        nest_success = 1;
        nest_success_selected = true;
        enable_submit();
    });

    $('#nest_success_unsure').click(function() {
        nest_success = 0;
        nest_success_selected = true;
        enable_submit();
    });

    $('#nest_success_no').click(function() {
        nest_success = -1;
        nest_success_selected = true;
        enable_submit();
    });

    $('#interesting_yes').click(function() {
        interesting = 1;
        interesting_selected = true;
        enable_submit();
    });

    $('#interesting_no').click(function() {
        interesting = -1;
        interesting_selected = true;
        enable_submit();
    });

    $('#another-site-button').click(function() {
            window.location.href = "http://volunteer.cs.und.edu/wildlife/video_selector.php";
    });

    $('#another-video-button').click(function() {
            window.location.reload();
    });

    function print_modal_row(row_name, col_name, post_observation, db_observations) {
        var body_text = "<tr>";
        body_text += "<td>" + row_name + ":</td>";
        var arr = new Array(1);
        arr[0] = post_observation[col_name];

        for (var i = 0; i < db_observations.length; i++) {
            arr.push( db_observations[i][col_name] );
        }

        for (var i = 0; i < arr.length; i++) {
            if (col_name === 'comments') {
                body_text += "<td>" + arr[i] + "</td>";
            } else {
                if (arr[i] < 0) {
                    body_text += "<td>no</td>";
                } else if (arr[i] === 0) {
                    body_text += "<td>unsure</td>";
                } else {
                    body_text += "<td>yes</td>";
                }
            }
        }
        body_text += "</tr>";
        return body_text;
    }

    $('#submit_button').click(function() {
        if (!$('#submit_button').hasClass("disabled")) {
            var comments_html = $('#comments').val();

            var submission_data = {
                user_id : user_id,
                video_segment_id : video_segment_id,
                comments : comments_html,
                bird_leave : bird_leave,
                bird_return : bird_return,
                bird_presence : bird_presence,
                bird_absence : bird_absence,
                predator_presence : predator_presence,
                nest_defense : nest_defense,
                nest_success : nest_success,
                interesting : interesting
            };

//            alert( JSON.stringify(submission_data) );

            $.ajax({
                type: 'POST',
                url: './report_observation.php',
                data : submission_data,
                dataType : 'json',
                success : function(response) {
                    var body_text = "";

                    if (response.post_observation.status === "CANONICAL" || response.post_observation.status == "VALID") {
                        body_text += "<p><b>Your observations were successfully validated!</b></p>";
                        body_text += "<p><b>You have been awarded " + response.post_observation.credit + " credit.<b></p>";
                    } else if (response.post_observation.status === "INVALID") {
                        body_text += "<p><b>Your observations did not match ones from the other users.</b></p>";
                    } else {
                        body_text += "<p><b>We still need other observations to validate yours.</b>";
                        body_text += "<p><b>You will be awarded credit later if it validates sucessfully.</b>";
                    }

                    if (!(response.post_observation.status === "UNVALIDATED")) {
                        body_text += "<p>Here is how your observations compare to the other users:</p>";
                        body_text += "<table class='table table-bordered table-striped'>";
                        body_text += "<tr>";
                        body_text += "<td></td>";
                        body_text += "<td><b>You</b></td>";
                        for (var i = 0; i <response.db_observations.length; i++) {
                            body_text += "<td>" + response.db_observations[i]['user_name'] + "</td>";
                        }
                        body_text += "</tr>";

                        body_text += print_modal_row('Bird left the nest', 'bird_leave', response.post_observation, response.db_observations);
                        body_text += print_modal_row('Bird returns to the nest', 'bird_return', response.post_observation, response.db_observations);
                        body_text += print_modal_row('Bird incubating the nest', 'bird_presence', response.post_observation, response.db_observations);
                        body_text += print_modal_row('Bird absent from the nest', 'bird_absence', response.post_observation, response.db_observations);
                        body_text += print_modal_row('Predator at the nest', 'predator_presence', response.post_observation, response.db_observations);
                        body_text += print_modal_row('Nest defense', 'nest_defense', response.post_observation, response.db_observations);
                        body_text += print_modal_row('Nest success', 'nest_success', response.post_observation, response.db_observations);
                        body_text += print_modal_row('Interesting', 'interesting', response.post_observation, response.db_observations);
                        body_text += print_modal_row('Comments', 'comments', response.post_observation, response.db_observations);

                        body_text += "</table>";
                    }

                    $('#submit-modal-body').html(body_text);
                    $('#submit-modal').modal( {keyboard: false} );
                },
                async: true
            });
        }
    });

});



