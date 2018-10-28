<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $project->project }}</title>
    <!-- Latest compiled and minified plotly.js JavaScript -->
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>


</head>
<body>
<div id="plot"></div>
<div id="plot2"></div>
<script>
    // Plotly.d3.csv("https://raw.githubusercontent.com/plotly/datasets/master/finance-charts-apple.csv", function(err, rows){
    //
    //     function unpack(rows, key) {
    //         return rows.map(function(row) { return row[key]; });
    //     }
    //
    //
    //     var trace1 = {
    //         type: "scatter",
    //         mode: "lines",
    //         name: 'AAPL High',
    //         x: unpack(rows, 'Date'),
    //         y: unpack(rows, 'AAPL.High'),
    //         line: {color: '#17BECF'}
    //     }
    //
    //     var trace2 = {
    //         type: "scatter",
    //         mode: "lines",
    //         name: 'AAPL Low',
    //         x: unpack(rows, 'Date'),
    //         y: unpack(rows, 'AAPL.Low'),
    //         line: {color: '#7F7F7F'}
    //     }
    //
    //     var data = [trace1,trace2];
    //     console.log(data);
    //     var layout = {
    //         title: 'Basic Time Series',
    //     };
    //
    //     Plotly.newPlot('plot', data, layout);
    // })

    function getFirstData() {
        var ajaxurl = "{{ route('project.responses', [$project->id]) }}";


        var time = new Date;
        time.setHours(00);
        time.setMinutes(00);
        time.setSeconds(00);
        time.setMilliseconds(00);

        jQuery.ajax({
            type: "get",
            url: ajaxurl,
            dataType : 'JSON',
            cache: "false",
            success: function(response){
                if(response.success) {

                    var channel_time = [];
                    var ySMS = [];
                    var yWeb = [];
                    jQuery.each(response.data, function (index, value) {

                        channel_time.push(value.time_slice);

                        if(value.channel == 'sms') {
                            ySMS.push(value.channel_count);

                        } else {
                            ySMS.push(0);
                        }
                        if(value.channel == 'web') {
                            yWeb.push(value.channel_count);
                        } else {
                            yWeb.push(0);
                        }

                    });
                    trace1 = {
                        type: "linear",
                        //mode: "lines",
                        name: 'SMS',
                        x: channel_time,
                        y: ySMS,
                        line: {color: '#17BECF'}
                    }

                    trace2 = {
                        type: "linear",
                        //mode: "lines",
                        name: 'Web',
                        x: channel_time,
                        y: yWeb,
                        line: {color: '#7F7F7F'}
                    }
                    drawChart(trace1,trace2);
                }
            }
        });
    }
    var trace1 = {};
    var trace2 = {};
    getFirstData();

    function drawChart(trace1,trace2) {
        var data = [trace1,trace2];
        //console.log(data);

        var layout = {
            title: '{{ $project->project }} response rate by report channel',
        };

        Plotly.newPlot('plot2', data, layout);
    }

    setInterval(function(){getFirstData();}, 3000);
</script>
</body>
</html>