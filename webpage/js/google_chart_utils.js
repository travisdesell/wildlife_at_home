google.load("visualization", "1", {packages: ["timeline"]});

function getDate(date_string) {
    if (typeof date_string === 'string') {
        var a = date_string.split(/[- :]/);
        return new Date(a[0], a[1]-1, a[2], a[3] || 0, a[4] || 0, a[5] || 0);
    }
    return null;
}

function onlyUnique(value, index, self) {
        return self.indexOf(value) === index;
}

function drawReviewTimeline(video_id, event_data) {
    var temp = new Array();
    for (var i = 0; i < event_data.length; i++) {
        temp.push(event_data[i][0]);
    }
    var unique_users = temp.filter(onlyUnique);
    var num_users = unique_users.length;

    var container = document.getElementById(video_id + '_review_timeline');
    var chart = new google.visualization.Timeline(container);
    var data = new google.visualization.DataTable();
    data.addColumn({type: 'string', id: 'Name'});
    data.addColumn({type: 'string', id: 'Event Type'});
    data.addColumn({type: 'date', id: 'Start'});
    data.addColumn({type: 'date', id: 'End'});
    data.addRows(event_data);
    var options = {
        height: (num_users) * 50 + 110
    };
    chart.draw(data, options);
}

function drawWatchTimeline(video_id, event_data) {
    var container = document.getElementById(video_id + '_watch_timeline');
    var chart = new google.visualization.Timeline(container);
    var data = new google.visualization.DataTable();
    data.addColumn({type: 'string', id: 'Event Type'});
    data.addColumn({type: 'date', id: 'Start'});
    data.addColumn({type: 'date', id: 'End'});
    data.addRows(event_data);
    var options = {
        height: 160
    };
    google.visualization.events.addListener(chart, 'select', function() {
        var video_id = $('.fast-forward-button').attr('video_id');
        var video = $('#wildlife-video-' + video_id).get(0);

        var selection = chart.getSelection();
        for (var i = 0; i < selection.length; i++) {
            var item = selection[i];
            if (item.row != null) {
                var video_start_string = $("#wildlife-video-" + video_id).attr("start_time");
                var video_start_time = getDate(video_start_string);
                //console.log("Start time: " + video_start_time);
                var event_start_time = data.getValue(item.row, 1);
                var seconds = (event_start_time - video_start_time)/1000 - 10;
            }
        }
        video.currentTime = seconds;
    });
    chart.draw(data, options);
}

