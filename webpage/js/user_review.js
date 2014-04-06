function enable_user_review() {
    var report_comments_default = "Please describe why you are reporting this observation (required).";

    $('.report-comments:not(.bound2)').addClass('bound2').each(function() {
        if ($.trim( $(this).val() ) == '') {
            $(this).addClass('default_comments_text');
            $("#submit-report-button-" + $(this).attr("video_id")).addClass("disabled");
            $(this).val(report_comments_default);
        }
    });

    $('.report-comments:not(.bound3)').addClass('bound3').blur(function() {
        if ($.trim( $(this).val() ) == '') {
            $(this).addClass('default_comments_text');
            $("#submit-report-button-" + $(this).attr("video_id")).addClass("disabled");
            $(this).val(report_comments_default);
        }
    });

    $('.report-comments:not(.bound-change)').addClass('bound-change').bind('input propertychange', function() {
        var observation_id = $("#report-comments-" + video_id).attr("observation_id");

        if ($.trim( $(this).val() ) == '') {
            $(this).addClass('default_comments_text');
            $("#submit-report-button-" + $(this).attr("video_id")).addClass("disabled");
            $(this).val(report_comments_default);

            $(".report-observation-button[observation_id=" + observation_id + "]").attr("report_comments_text", "");
        } else {
            $(this).removeClass('default_comments_text');
            $("#submit-report-button-" + $(this).attr("video_id")).removeClass("disabled");

            $(".report-observation-button[observation_id=" + observation_id + "]").attr("report_comments_text", $(this).val());
        }
    });

    $('.report-observation-button:not(.bound)').addClass('bound').click(function() {
        var video_id = $(this).attr("video_id");

        if ($(this).hasClass('active')) {
            $("#report-comments-div-" + video_id).addClass("hidden");
            $("#report-comments-" + video_id).val("");
            $("#report-comments-" + video_id).attr("observation_id", -1);
            $("#report-comments-title-" + video_id).text("Reporting observation:");
        } else {
            $(".rob-" + video_id).removeClass('active');
            $("#report-comments-div-" + video_id).removeClass("hidden");

            var observation_id = $(this).attr("observation_id");
            $("#report-comments-" + video_id).attr("observation_id", observation_id);

            var comments_attr = $(this).attr("report_comments_text");
            if (comments_attr === undefined || comments_attr === false || comments_attr === '') {
                $("#report-comments-" + video_id).val(report_comments_default);
                $("#report-comments-" + video_id).addClass('default_comments_text');
            } else {
                $("#report-comments-" + video_id).val(comments_attr);
                $("#report-comments-" + video_id).removeClass('default_comments_text');
            }

            var status_attr = $(this).attr("report_status");
            if (status_attr === undefined || status_attr === false || status_attr === '' || status_attr === "UNREPORTED") {
                $("#submit-report-button-" + video_id).text("Submit Report");
                $("#submit-report-button-" + video_id).addClass("btn-success");
                $("#submit-report-button-" + video_id).removeClass("btn-warning");
                $("#submit-report-button-" + video_id).removeClass("hidden");
                $("#report-comments-" + video_id).attr('readonly',false);

                $("#response-comments-div-" + video_id).addClass("hidden");
            } else if (status_attr === "REPORTED") {
                $("#submit-report-button-" + video_id).text("Pending Response");
                $("#submit-report-button-" + video_id).removeClass("btn-success");
                $("#submit-report-button-" + video_id).addClass("btn-warning");
                $("#submit-report-button-" + video_id).addClass("disabled");
                $("#submit-report-button-" + video_id).removeClass("hidden");
                $("#report-comments-" + video_id).attr('readonly',true);

                $("#response-comments-div-" + video_id).addClass("hidden");
            } else {    //status_attr === "responded"
                $("#submit-report-button-" + video_id).addClass("hidden");
                $("#report-comments-" + video_id).attr('readonly',true);

                $("#response-comments-div-" + video_id).removeClass("hidden");
                $("#response-comments-title-" + video_id).text($(this).attr("responder_name") + " responded with:");
                $("#response-comments-" + video_id).text($(this).attr("response_comments_text"));
            }

            var reporter_name = $(this).attr("reporter_name");
            if (reporter_name === undefined || reporter_name === false || reporter_name === '') {
                $("#report-comments-title-" + video_id).text("Reporting observation " + $(this).attr("observation_id") + ":");
            } else {
                $("#report-comments-title-" + video_id).text(reporter_name + " reported observation " + $(this).attr("observation_id") + ":");
            }
        }

        $(this).toggleClass('active');
    });

    $('.submit-report-button:not(.bound)').addClass('bound').click(function() {
        if ($(this).hasClass("disabled")) return;

        $(this).addClass("disabled");
        $(this).text("Submitting...");

        var submit_report_button = $(this);
        var video_id = $(this).attr("video_id");
        var observation_id = $("#report-comments-" + video_id).attr("observation_id");

        $(".report-observation-button[observation_id=" + observation_id + "]").removeClass("btn-danger");
        $(".report-observation-button[observation_id=" + observation_id + "]").addClass("btn-warning");
        $(".report-observation-button[observation_id=" + observation_id + "]").attr("report_status", "REPORTED");
        $(".report-observation-button[observation_id=" + observation_id + "]").attr("reporter_name", user_name);
        $("#report-comments-title-" + video_id).text(user_name + " reported observation " + observation_id);

        $.ajax({
            type: 'POST',
            url: './review_interface/submit_report.php',
            data : {
                observation_id : observation_id,
                comments : $("#report-comments-" + video_id).val()
            },
            dataType : 'text',
            success : function(response) {
                console.log("the response was:\n" + response);

                submit_report_button.removeClass("btn-success");
                submit_report_button.addClass("btn-warning");
                submit_report_button.text("Pending Response");
            },
            error : function(jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            },
            async: true
        });
    });
}
