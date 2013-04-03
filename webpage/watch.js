
$(document).ready(function () {
    $('#fast_forward_button').button();
    $('#fast_backward_button').button();
    $('#too_dark_button').button();
    $('#corrupt_button').button();

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
     * Use this to create a forum thread with the video.
     */
    var discuss_video_clicked = false;
    $('#discuss-video-button').button();
    $('#discuss-video-button').click(function() { 
            discuss_video_clicked = true;
            $("#discuss-video-form").submit();
    });
//    $('#discuss-video-button-too-dark').button();
//    $('#discuss-video-button-too-dark').click(function() { $("#discuss-video-form-too-dark").submit(); });
//    $('#discuss-video-button-corrupt').button();
//    $('#discuss-video-button-corrupt').click(function() { $("#discuss-video-form-corrupt").submit(); });



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
    var chick_presence_selected = false;
    var interesting_selected = false;

    $('#bird_leave_yes').prop('checked', false);
    $('#bird_leave_unsure').prop('checked', false);
    $('#bird_leave_no').prop('checked', false);
    $('#bird_return_yes').prop('checked', false);
    $('#bird_return_unsure').prop('checked', false);
    $('#bird_return_no').prop('checked', false);
    $('#bird_absence_yes').prop('checked', false);
    $('#bird_absence_unsure').prop('checked', false);
    $('#bird_absence_no').prop('checked', false);
    $('#bird_presence_yes').prop('checked', false);
    $('#bird_presence_unsure').prop('checked', false);
    $('#bird_presence_no').prop('checked', false);
    $('#predator_presence_yes').prop('checked', false);
    $('#predator_presence_unsure').prop('checked', false);
    $('#predator_presence_no').prop('checked', false);
    $('#nest_defense_yes').prop('checked', false);
    $('#nest_defense_unsure').prop('checked', false);
    $('#nest_defense_no').prop('checked', false);
    $('#nest_success_yes').prop('checked', false);
    $('#nest_success_unsure').prop('checked', false);
    $('#nest_success_no').prop('checked', false);
    $('#chick_presence_yes').prop('checked', false);
    $('#chick_presence_unsure').prop('checked', false);
    $('#chick_presence_no').prop('checked', false);
    $('#interesting_yes').prop('checked', false);
    $('#interesting_no').prop('checked', false);


    $('#comments').val("");

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
    var chick_presence = 0;
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
            predator_presence_selected && nest_defense_selected && nest_success_selected && chick_presence_selected && interesting_selected) {

            $('#submit_button').removeClass("disabled");
        }
    }

    function fake_radio(name, which) {
        if (which === 'yes') $('#' + name + '_yes').addClass("active");
        else $('#' + name + '_yes').removeClass("active");

        if (name != 'interesting') {
            if (which === 'unsure') $('#' + name + '_unsure').addClass("active");
            else $('#' + name + '_unsure').removeClass("active");
        }

        if (which === 'no') $('#' + name + '_no').addClass("active");
        else $('#' + name + '_no').removeClass("active");
    }

    $('#bird_leave_yes').click(function() {
        fake_radio('bird_leave', 'yes');
        bird_leave = 1;
        bird_leave_selected = true;
        enable_submit();
    });

    $('#bird_leave_unsure').click(function() {
        fake_radio('bird_leave', 'unsure');
        bird_leave = 0;
        bird_leave_selected = true;
        enable_submit();
    });

    $('#bird_leave_no').click(function() {
        fake_radio('bird_leave', 'no');
        bird_leave = -1;
        bird_leave_selected = true;
        enable_submit();
    });

    $('#bird_return_yes').click(function() {
        fake_radio('bird_return', 'yes');
        bird_return = 1;
        bird_return_selected = true;
        enable_submit();
    });

    $('#bird_return_unsure').click(function() {
        fake_radio('bird_return', 'unsure');
        bird_return = 0;
        bird_return_selected = true;
        enable_submit();
    });

    $('#bird_return_no').click(function() {
        fake_radio('bird_return', 'no');
        bird_return = -1;
        bird_return_selected = true;
        enable_submit();
    });

    $('#bird_absence_yes').click(function() {
        fake_radio('bird_absence', 'yes');
        bird_absence = 1;
        bird_absence_selected = true;
        enable_submit();
    });

    $('#bird_absence_unsure').click(function() {
        fake_radio('bird_absence', 'unsure');
        bird_absence = 0;
        bird_absence_selected = true;
        enable_submit();
    });

    $('#bird_absence_no').click(function() {
        fake_radio('bird_absence', 'no');
        bird_absence = -1;
        bird_absence_selected = true;
        enable_submit();
    });

    $('#bird_presence_yes').click(function() {
        fake_radio('bird_presence', 'yes');
        bird_presence = 1;
        bird_presence_selected = true;
        enable_submit();
    });

    $('#bird_presence_unsure').click(function() {
        fake_radio('bird_presence', 'unsure');
        bird_presence = 0;
        bird_presence_selected = true;
        enable_submit();
    });

    $('#bird_presence_no').click(function() {
        fake_radio('bird_presence', 'no');
        bird_presence = -1;
        bird_presence_selected = true;
        enable_submit();
    });

    $('#predator_presence_yes').click(function() {
        fake_radio('predator_presence', 'yes');
        predator_presence = 1;
        predator_presence_selected = true;
        enable_submit();
    });

    $('#predator_presence_unsure').click(function() {
        fake_radio('predator_presence', 'unsure');
        predator_presence = 0;
        predator_presence_selected = true;
        enable_submit();
    });

    $('#predator_presence_no').click(function() {
        fake_radio('predator_presence', 'no');
        predator_presence = -1;
        predator_presence_selected = true;
        enable_submit();
    });

    $('#nest_defense_yes').click(function() {
        fake_radio('nest_defense', 'yes');
        nest_defense = 1;
        nest_defense_selected = true;
        enable_submit();
    });

    $('#nest_defense_unsure').click(function() {
        fake_radio('nest_defense', 'unsure');
        nest_defense = 0;
        nest_defense_selected = true;
        enable_submit();
    });

    $('#nest_defense_no').click(function() {
        fake_radio('nest_defense', 'no');
        nest_defense = -1;
        nest_defense_selected = true;
        enable_submit();
    });

    $('#nest_success_yes').click(function() {
        fake_radio('nest_success', 'yes');
        nest_success = 1;
        nest_success_selected = true;
        enable_submit();
    });

    $('#nest_success_unsure').click(function() {
        fake_radio('nest_success', 'unsure');
        nest_success = 0;
        nest_success_selected = true;
        enable_submit();
    });

    $('#nest_success_no').click(function() {
        fake_radio('nest_success', 'no');
        nest_success = -1;
        nest_success_selected = true;
        enable_submit();
    });

    $('#chick_presence_yes').click(function() {
        fake_radio('chick_presence', 'yes');
        chick_presence = 1;
        chick_presence_selected = true;
        enable_submit();
    });

    $('#chick_presence_unsure').click(function() {
        fake_radio('chick_presence', 'unsure');
        chick_presence = 0;
        chick_presence_selected = true;
        enable_submit();
    });

    $('#chick_presence_no').click(function() {
        fake_radio('chick_presence', 'no');
        chick_presence = -1;
        chick_presence_selected = true;
        enable_submit();
    });


    $('#interesting_yes').click(function() {
        fake_radio('interesting', 'yes');
        interesting = 1;
        interesting_selected = true;
        enable_submit();
    });

    $('#interesting_no').click(function() {
        fake_radio('interesting', 'no');
        interesting = -1;
        interesting_selected = true;
        enable_submit();
    });

    var another_site_clicked = false;
    $('#another-site-button').click(function() {
            another_site_clicked = true;
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
            console.log("arr[" + i + "] is: " + arr[i]);

            if (col_name === 'comments') {
                body_text += "<td>" + arr[i] + "</td>";
            } else {
                if (arr[i] == 0 || arr[i] == undefined || arr[i] == null) {
                    body_text += "<td>unsure</td>";
                } else if (arr[i] < 0) {
                    body_text += "<td>no</td>";
                } else {
                    body_text += "<td>yes</td>";
                }
            }
        }
        body_text += "</tr>";
        return body_text;
    }

    function generate_modal(modal_body, submission_data) {
        $.ajax({
            type: 'POST',
            url: './report_observation.php',
            data : submission_data,
            dataType : 'json',
            success : function(response) {
                var body_text = "";

                console.log("response.post_observation.status = " + response.post_observation.status);

                if (response.post_observation.status === "CANONICAL" || response.post_observation.status == "VALID") {
                    body_text += "<p><b>Your observations were successfully validated!</b></p>";
                    body_text += "<p><b>You have been awarded " + response.post_observation.credit + " credit.<b></p>";
                } else if (response.post_observation.status === "INVALID") {
                    body_text += "<p><b>Your observations did not match ones from the other users.</b></p>";
                } else {
                    body_text += "<p><b>We still need other observations to validate yours.</b>";
                    body_text += "<p><b>You will be awarded credit later if it validates sucessfully.</b>";
                }

//                if (!(response.post_observation.status === "UNVALIDATED")) {
                    body_text += "<p>Here is how your observations compare to the other users:</p>";
                    body_text += "<table class='table table-bordered table-striped'>";
                    body_text += "<tr>";
                    body_text += "<td></td>";
                    body_text += "<td><b>You</b></td>";
                    for (var i = 0; i <response.db_observations.length; i++) {
                        body_text += "<td>" + response.db_observations[i]['user_name'] + "</td>";
                    }
                    body_text += "</tr>";

                    if (response.post_observation.too_dark == 1) {
                        body_text += print_modal_row('Too dark', 'too_dark', response.post_observation, response.db_observations);
                    } else if (response.post_observation.corrupt == 1) {
                        body_text += print_modal_row('Corrupt', 'corrupt', response.post_observation, response.db_observations);
                    } else {
                        body_text += print_modal_row('Bird left the nest', 'bird_leave', response.post_observation, response.db_observations);
                        body_text += print_modal_row('Bird returns to the nest', 'bird_return', response.post_observation, response.db_observations);
                        body_text += print_modal_row('Bird incubating the nest', 'bird_presence', response.post_observation, response.db_observations);
                        body_text += print_modal_row('Bird absent from the nest', 'bird_absence', response.post_observation, response.db_observations);
                        body_text += print_modal_row('Predator at the nest', 'predator_presence', response.post_observation, response.db_observations);
                        body_text += print_modal_row('Nest defense', 'nest_defense', response.post_observation, response.db_observations);
                        body_text += print_modal_row('Nest success', 'nest_success', response.post_observation, response.db_observations);
                        body_text += print_modal_row('Chicks at the nest', 'chick_presence', response.post_observation, response.db_observations);
                        body_text += print_modal_row('Interesting', 'interesting', response.post_observation, response.db_observations);
                    }
                    body_text += print_modal_row('Comments', 'comments', response.post_observation, response.db_observations);

                    body_text += "</table>";
//                }

                console.log("showing modal: '" + modal_body + "'");

                $(modal_body + '-body').html(body_text);
                $(modal_body).modal( {keyboard: false} );
            },
            async: true
        });
    }



    $('#corrupt_button').click(function() {
        console.log("corrupt!");

        if (!$('#corrupt_button').hasClass("disabled")) {
            $('#corrupt_button').addClass("disabled");

            var body_text = "You flagged the video as corrupt.";

            var comments_html = $('#comments').val();
            if (!interesting_selected) interesting = -1;
            var submission_data = {
                user_id : user_id,
                video_segment_id : video_segment_id,
                comments : comments_html,
                bird_leave : 0,
                bird_return : 0,
                bird_presence : 0,
                bird_absence : 0,
                predator_presence : 0,
                nest_defense : 0,
                nest_success : 0,
                chick_presence: 0,
                interesting : -1,
                start_time : start_time,
                species_id : species_id,
                location_id : location_id,
                duration_s : duration_s,
                too_dark : 0,
                corrupt : 1
            };

            var modal_body = '#submit-modal';

            console.log("flagging video as corrupt, generating modal: '" + modal_body + "'");
            generate_modal(modal_body, submission_data);

            $('#corrupt_button').removeClass("disabled");
        }
    });

    $('#too_dark_button').click(function() {
        console.log("too dark!");

        if (!$('#too_dark_button').hasClass("disabled")) {
            $('#too_dark_button').addClass("disabled");

            var body_text = "You flagged the video as too dark.";

            var comments_html = $('#comments').val();

            if (!interesting_selected) interesting = -1;
            var submission_data = {
                user_id : user_id,
                video_segment_id : video_segment_id,
                comments : comments_html,
                bird_leave : 0,
                bird_return : 0,
                bird_presence : 0,
                bird_absence : 0,
                predator_presence : 0,
                nest_defense : 0,
                nest_success : 0,
                chick_presence: 0,
                interesting : interesting,
                start_time : start_time,
                species_id : species_id,
                location_id : location_id,
                duration_s : duration_s,
                too_dark : 1,
                corrupt : 0
            };

            var modal_body = '#submit-modal';

            console.log("flagging video as too dark, generating modal: '" + modal_body + "'");
            generate_modal(modal_body, submission_data);

            $('#too_dark_button').removeClass("disabled");
        }
    });



    $('#submit_button').click(function() {
        if (!$('#submit_button').hasClass("disabled")) {
            $('#submit_button').addClass("disabled");
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
                chick_presence: chick_presence,
                interesting : interesting,
                start_time : start_time,
                species_id : species_id,
                location_id : location_id,
                duration_s : duration_s,
                too_dark : 0,
                corrupt : 0
            };

//            alert( JSON.stringify(submission_data) );

            var modal_body = '#submit-modal';

            console.log("submitting observations, generating modal: '" + modal_body + "'");
            generate_modal(modal_body, submission_data);
        }
    });

    /**
     *  Reload the window when the modal is esacped so a user cant enter
     *  observations twice for the same video.
     */
    $('#submit-modal').on('hidden', function () {
        if (!another_site_clicked && !discuss_video_clicked) {
            window.location.reload();
        }
    });
});



