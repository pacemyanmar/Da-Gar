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

        var start_time = "{!! $start_time !!}"
        jQuery.ajax({
            type: "get",
            url: ajaxurl,
            dataType : 'JSON',
            cache: "false",
            success: function(response){
                if(response.success) {
                    var ySMS = [0];
                    var yWeb = [0];

                    var sms_channel_time = [start_time];
                    var web_channel_time = [start_time];

                    jQuery.each(response.data, function (index, value) {

                        if(value.channel == 'sms') {
                            sms_channel_time.push(value.sms_time_slice);
                            ySMS.push(value.sms_channel_count);

                        }

                        if(value.channel == 'web') {
                            web_channel_time.push(value.web_time_slice);
                            yWeb.push(value.web_channel_count);
                        }

                    });
                    trace1 = {
                        type: "linear",
                        //mode: "lines",
                        name: 'SMS',
                        x: sms_channel_time,
                        y: ySMS,
                        line: {color: '#17BECF'}
                    }

                    trace2 = {
                        type: "linear",
                        //mode: "lines",
                        name: 'Web',
                        x: web_channel_time,
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
        console.log(data);

        var layout = {
            title: '{{ $project->project }} response rate by report channel',
        };

        Plotly.newPlot('plot2', data, layout);
    }

    setInterval(function(){getFirstData();}, 3000);





    function drawSectionChart(section_data, section_title, section_id) {
        var data = [section_data];
        console.log(data);

        var layout = {
            title: section_title,
        };

        Plotly.newPlot(section_id, data, layout);
    }

    function getSectionData(ajaxurl, section_title, section_id) {

        var start_time = "{!! $start_time !!}"

        jQuery.ajax({
            type: "get",
            url: ajaxurl,
            dataType : 'JSON',
            cache: "false",
            success: function(response){
                if(response.success) {
                    var ySMS = [0];

                    var sms_channel_time = [start_time];

                    jQuery.each(response.data, function (index, value) {

                            sms_channel_time.push(value.time);
                            ySMS.push(value.count);

                    });
                    section_data = {
                        type: "linear",
                        mode: "lines",
                        name: 'SMS',
                        x: sms_channel_time,
                        y: ySMS,
                        line: {color: '#17BECF'}
                    }

                    drawSectionChart(section_data, section_title, section_id);
                }
            }
        });

    }

</script>

@foreach($project->sections as $section)
    <div id="section{{$section->sort}}"></div>
        <script>

            var section_data{{$section->sort}} = {};

            getSectionData("{{ route('smscount', [$project->id, $section->sort]) }}", "{{ $section->sectionname }}", "section{{$section->sort}}");

           setInterval(function(){getSectionData("{{ route('smscount', [$project->id, $section->sort]) }}", "{{ $section->sectionname }}", "section{{$section->sort}}");}, 10000);
        </script>

@endforeach
</body>
</html>