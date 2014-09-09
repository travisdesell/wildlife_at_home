function enable_expert_review_comments() {
    var report_comments_default = "Please provide a response to the reported observation, or provide a description of why you are changing observation's validation status.";

    $('.response-comments:not(.bound2)').addClass('bound2').each(function() {
        if ($.trim( $(this).val() ) == '') {
            $(this).addClass('default_comments_text');
            $(".submit-response-button[observation_id=" + $(this).attr("observation_id") + "]").addClass("disabled");
            $(this).val(report_comments_default);
        }
    });

    $('.response-comments:not(.bound3)').addClass('bound3').blur(function() {
        if ($.trim( $(this).val() ) == '') {
            $(this).addClass('default_comments_text');
            $(".submit-response-button[observation_id=" + $(this).attr("observation_id") + "]").addClass("disabled");
            $(this).val(report_comments_default);
        }
    });

    $('.response-comments:not(.bound-change)').addClass('bound-change').bind('input propertychange', function() {
        if ($.trim( $(this).val() ) == '') {
            $(this).addClass('default_comments_text');
            $(".submit-response-button[observation_id=" + $(this).attr("observation_id") + "]").addClass("disabled");
            $(".expert-respond-button[observation_id=" + $(this).attr("observation_id") + "]").attr("response_comments_text", "");
            $(this).val(report_comments_default);
        } else {
            $(this).removeClass('default_comments_text');
            $(".expert-respond-button[observation_id=" + $(this).attr("observation_id") + "]").attr("response_comments_text", $(this).val());

            if ($(".edit-status-button[observation_id=" + $(this).attr("observation_id") + "]").attr("status") !== "") {
                $(".submit-response-button[observation_id=" + $(this).attr("observation_id") + "]").removeClass("disabled");
            }
        }
    });

    $('.edit-status-dropdown:not(.bound)').addClass('bound').click(function() {
        $(".edit-status-button[observation_id=" + $(this).attr("observation_id") + "]").html( $(this).attr("status") + "<span class='caret'></span>" );
        $(".edit-status-button[observation_id=" + $(this).attr("observation_id") + "]").attr( "status", $(this).attr("status") );

        var val = $(".response-comments[observation_id=" + $(this).attr("observation_id") + "]").val();
        if (val !== report_comments_default) {
            $(".submit-response-button[observation_id=" + $(this).attr("observation_id") + "]").removeClass("disabled");
        }
    });

    $('.submit-response-button:not(.bound)').addClass('bound').click(function() {
        if ($(this).hasClass("disabled")) return;

        $(this).addClass("disabled");
        $(this).text("Submitting...");
        var submit_response_button = $(this);

        var observation_id = $(this).attr("observation_id");
        var response_comments = $(".response-comments[observation_id=" + observation_id + "]").val();
        var validation_status = $(".edit-status-dropdown[observation_id=" + observation_id + "]").attr("status");

        var submission_data = {
            observation_id : observation_id,
            response_comments : response_comments,
            validation_status : validation_status
        };

        $.ajax({
            type: 'POST',
            url: './review_interface/submit_response.php',
            data : submission_data,
            dataType : 'text',
            success : function(response) {
                submit_response_button.removeClass("disabled");
                submit_response_button.text("Submit Response");

                $(".expert-respond-button[observation_id=" + observation_id +"]").html("<i class='icon-ok-sign icon-white'></i>");
                $(".expert-respond-button[observation_id=" + observation_id +"]").addClass("btn-success");
                $(".expert-respond-button[observation_id=" + observation_id +"]").removeClass("btn-warning");
            },
            error : function(jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            },
            async: true
        });


    });

}

function enable_expert_review() {
    $('.expert-respond-button:not(.bound)').addClass('bound').click(function() {
        $(this).toggleClass("active");

        if ($(this).hasClass("active")) {
            var row = $(this).closest("tr");
            console.log(row);

            var observation_id = $(this).attr("observation_id");

            var report_comments = $(this).attr("report_comments_text");
            var reporter_name = $(this).attr("reporter_name");

            var response_comments = $(this).attr("response_comments_text");
            var responder_name = $(this).attr("responder_name");

            console.log("response comments are: '" + response_comments + "'");
            console.log("responder name: '" + responder_name + "'");

            var report_part = "";
            if (reporter_name === '' || reporter_name === undefined || reporter_name === false) {
                report_part = "<b>Observation " + observation_id + " has not been reported.</b>";
                report_part += "<textarea readonly rows=4 style='width:97%'></textarea>";
            } else {
                report_part = "<b>" + reporter_name + " reported observation " + observation_id + ":</b>";
                report_part += "<textarea readonly rows=4 style='width:97%'>" + report_comments + "</textarea>";
            }

            var response_part = "";
            if (responder_name === '' || responder_name === undefined || responder_name === false) {
                if (reporter_name === '' || reporter_name === undefined || reporter_name === false) {
                    response_part = "<b>You can comment on this observation and edit its status:</b>";
                } else {
                    response_part = "<b>Please enter your response:</b>";
                }
            } else {
                if (reporter_name === '' || reporter_name === undefined || reporter_name === false) {
                    response_part = "<b>" + responder_name + " commented on observation " + observation_id + ":</b>";
                } else {
                    response_part = "<b>" + responder_name + " responded to observation " + observation_id + ":</b>";
                }
            }

            if (response_comments === '' || response_comments === undefined || response_comments === false) {
                response_part += "<textarea class='response-comments' rows=4 style='width:97%; margin-bottom:5px;' observation_id=" + observation_id + "> </textarea>";
            } else {
                response_part += "<textarea class='response-comments' rows=4 style='width:97%; margin-bottom:5px;' observation_id=" + observation_id + ">" + response_comments + "</textarea>";
            }

            response_part += "<button class='btn btn-small btn-primary pull-right submit-response-button disabled' style='margin-bottom:5px;' observation_id=" + observation_id + ">Submit Response</button>";

            response_part += "<div class='btn-group pull-left' style='margin-left:5px;'>";
            response_part += "<button type='button' class='btn btn-small dropdown-toggle edit-status-button' data-toggle='dropdown' style='width:100%; text-align:right;' status='' observation_id=" + observation_id + ">Set Status<span class='caret'></span> </button>";
            response_part += "<ul class='dropdown-menu'>";
            response_part += "<li><a href='javascript:;' class='edit-status-dropdown' observation_id=" + observation_id + " status='UNVALIDATED'>UNVALIDATED</a></li>";
            response_part += "<li><a href='javascript:;' class='edit-status-dropdown' observation_id=" + observation_id + " status='VALID'>VALID</a></li>";
            response_part += "<li><a href='javascript:;' class='edit-status-dropdown' observation_id=" + observation_id + " status='INVALID'>INVALID</a></li>";
            response_part += "</ul>";
            response_part += "</div> <!--button group-->";

            row.after("<tr><td colspan=4>" + report_part + "</td><td colspan=4>" + response_part + "</td></tr>");

            enable_expert_review_comments();
        } else {
            var row = $(this).closest("tr");
            console.log(row);
            row.next().remove();
        }
    });
}
